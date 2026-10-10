<?php

namespace App\Models;

use App\Database\Database;

class LinkedInLead {
    /**
     * Columns `update()` is allowed to write.
     *
     * The dynamic SET builder below interpolates array keys straight into SQL, so
     * without this a single typo in a caller becomes a 500 (that is exactly how
     * `linkedin_search_url` broke campaign saving). Anything not listed here is
     * silently dropped rather than blowing up the request.
     */
    private const COLUMNS = [
        'campaign_id', 'linkedin_profile_url', 'linkedin_id', 'linkedin_username',
        'member_urn', 'first_name', 'last_name', 'headline', 'company', 'job_title',
        'location', 'email', 'phone', 'profile_image_url', 'industry', 'notes',
        'connection_status', 'connection_degree', 'network_distance', 'status',
        'source', 'current_step', 'score', 'heat_score', 'tags', 'customer_id',
        'converted_at', 'do_not_engage', 'archived_at', 'email_captured_at',
        'enriched_at', 'enrichment', 'has_history', 'engaged_your_content',
        'checked_resource', 'submitted_email', 'we_dmed', 'we_replied_to_comment',
        'we_sent_connection_request', 'first_touch_at', 'last_event_at',
        'last_contacted_at', 'last_replied_at',
    ];

    /**
     * `full_name` and `linkedin_url` are aliases, not stored columns.
     *
     * Both the views and the older calling code expect them, but storing a copy
     * would be a third source of truth that drifts from first_name/last_name and
     * from linkedin_profile_url. Aliasing keeps one source of truth and means the
     * existing views need no changes.
     */
    private const SELECT = "*,
            TRIM(CONCAT_WS(' ', first_name, last_name)) AS full_name,
            linkedin_profile_url AS linkedin_url,
            customer_id AS converted_customer_id";

    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT " . self::SELECT . " FROM linkedin_leads WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT " . self::SELECT . " FROM linkedin_leads WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    /**
     * Find a lead by any LinkedIn identifier — member URN, provider id, or slug.
     * This is the resolver the Outreach tools use before acting on a person.
     */
    public static function findByIdentifier($tenantId, string $identifier) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT " . self::SELECT . " FROM linkedin_leads
            WHERE tenant_id = ?
              AND (member_urn = ? OR linkedin_id = ? OR linkedin_username = ?)
            LIMIT 1
        ");
        $stmt->execute([$tenantId, $identifier, $identifier, $identifier]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $search = null, $limit = 50, $offset = 0) {
        $db = Database::getConnection();
        $sql = "SELECT " . self::SELECT . " FROM linkedin_leads WHERE tenant_id = ?";
        $params = [$tenantId];

        if ($search) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ?
                        OR CONCAT_WS(' ', first_name, last_name) LIKE ?
                        OR company LIKE ? OR job_title LIKE ? OR email LIKE ?
                        OR linkedin_profile_url LIKE ?)";
            $searchTerm = '%' . $search . '%';
            for ($i = 0; $i < 7; $i++) {
                $params[] = $searchTerm;
            }
        }

        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function allByCampaign($campaignId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT " . self::SELECT . " FROM linkedin_leads WHERE campaign_id = ? ORDER BY created_at DESC");
        $stmt->execute([$campaignId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        // Callers may pass a single full_name; split it rather than rejecting it.
        $firstName = $data['first_name'] ?? null;
        $lastName  = $data['last_name'] ?? null;
        if ($firstName === null && !empty($data['full_name'])) {
            $parts = explode(' ', trim($data['full_name']), 2);
            $firstName = $parts[0] ?? '';
            $lastName  = $parts[1] ?? null;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO linkedin_leads
                (tenant_id, campaign_id, first_name, last_name, headline, email, phone,
                 company, job_title, location, industry, notes, profile_image_url,
                 linkedin_profile_url, linkedin_id, linkedin_username, member_urn,
                 connection_status, connection_degree, network_distance, status, source,
                 created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['campaign_id'] ?? null,
            $firstName,
            $lastName,
            $data['headline'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['company'] ?? null,
            $data['job_title'] ?? null,
            $data['location'] ?? null,
            $data['industry'] ?? null,
            $data['notes'] ?? null,
            $data['profile_image_url'] ?? null,
            // Accept the legacy `linkedin_url` key as well as the real column name.
            $data['linkedin_profile_url'] ?? $data['linkedin_url'] ?? null,
            $data['linkedin_id'] ?? null,
            $data['linkedin_username'] ?? null,
            $data['member_urn'] ?? $data['linkedin_id'] ?? null,
            $data['connection_status'] ?? 'none',
            $data['connection_degree'] ?? null,
            $data['network_distance'] ?? null,
            $data['status'] ?? 'new',
            $data['source'] ?? 'manual',
        ]);
        return $db->lastInsertId();
    }

    /**
     * Create or update a lead keyed on the LinkedIn member URN.
     *
     * The post runner used to INSERT unconditionally after every successful DM, so
     * the same person commenting on two posts became two rows — which breaks
     * per-person enrichment caching, lets one human hold two different segments, and
     * makes "have we already messaged them?" unanswerable.
     *
     * Relies on the UNIQUE index added by migration 036a. `LAST_INSERT_ID(id)` is what
     * makes the statement return the existing row's id on the update branch; without
     * it PDO reports 0 for an update and the caller loses the lead.
     *
     * Only ever fills gaps: COALESCE keeps whatever we already knew and adds what we
     * did not. A later, richer observation never blanks an earlier one.
     *
     * @return int the lead id, or 0 when it could not be resolved
     */
    public static function upsertByUrn(int $tenantId, array $data): int {
        $memberUrn = $data['member_urn'] ?? $data['linkedin_id'] ?? null;

        // No stable identity — fall back to a plain insert. Nothing to dedupe against.
        if (empty($memberUrn)) {
            return (int) self::create(['tenant_id' => $tenantId] + $data);
        }

        $firstName = $data['first_name'] ?? null;
        $lastName  = $data['last_name'] ?? null;
        if ($firstName === null && !empty($data['full_name'])) {
            $parts = explode(' ', trim($data['full_name']), 2);
            $firstName = $parts[0] ?? '';
            $lastName  = $parts[1] ?? null;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO linkedin_leads
                (tenant_id, campaign_id, first_name, last_name, headline, company, job_title,
                 location, industry, email, profile_image_url, linkedin_profile_url,
                 linkedin_id, linkedin_username, member_urn, connection_status,
                 connection_degree, network_distance, status, source, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                id                   = LAST_INSERT_ID(id),
                first_name           = COALESCE(first_name, VALUES(first_name)),
                last_name            = COALESCE(last_name, VALUES(last_name)),
                headline             = COALESCE(headline, VALUES(headline)),
                company              = COALESCE(company, VALUES(company)),
                job_title            = COALESCE(job_title, VALUES(job_title)),
                location             = COALESCE(location, VALUES(location)),
                industry             = COALESCE(industry, VALUES(industry)),
                email                = COALESCE(email, VALUES(email)),
                profile_image_url    = COALESCE(profile_image_url, VALUES(profile_image_url)),
                linkedin_profile_url = COALESCE(NULLIF(linkedin_profile_url, ''), NULLIF(VALUES(linkedin_profile_url), '')),
                linkedin_username    = COALESCE(linkedin_username, VALUES(linkedin_username)),
                connection_degree    = COALESCE(connection_degree, VALUES(connection_degree)),
                network_distance     = COALESCE(network_distance, VALUES(network_distance)),
                updated_at           = NOW()
        ");
        $stmt->execute([
            $tenantId,
            $data['campaign_id'] ?? null,
            $firstName,
            $lastName,
            $data['headline'] ?? null,
            $data['company'] ?? null,
            $data['job_title'] ?? null,
            $data['location'] ?? null,
            $data['industry'] ?? null,
            $data['email'] ?? null,
            $data['profile_image_url'] ?? null,
            $data['linkedin_profile_url'] ?? $data['linkedin_url'] ?? null,
            $data['linkedin_id'] ?? $memberUrn,
            $data['linkedin_username'] ?? null,
            $memberUrn,
            $data['connection_status'] ?? 'none',
            $data['connection_degree'] ?? null,
            $data['network_distance'] ?? null,
            $data['status'] ?? 'new',
            $data['source'] ?? 'automation',
        ]);

        return (int) $db->lastInsertId();
    }

    public static function update($id, $data) {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            if (!in_array($key, self::COLUMNS, true)) {
                continue;
            }
            $fields[] = "`$key` = ?";
            $values[] = $value;
        }
        if (empty($fields)) {
            return false;
        }

        $db = Database::getConnection();
        $values[] = $id;
        $stmt = $db->prepare("UPDATE linkedin_leads SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM linkedin_leads WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function countByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM linkedin_leads WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['count'];
    }

    public static function countByCampaign($campaignId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM linkedin_leads WHERE campaign_id = ?");
        $stmt->execute([$campaignId]);
        return $stmt->fetch()['count'];
    }

    /**
     * Archive a lead. Writes both the lead flag and a do-not-engage suppression row,
     * so the block survives deletion of the lead itself.
     */
    public static function archive($id, $tenantId, ?int $userId = null): bool {
        $lead = self::find($id, $tenantId);
        if (!$lead) {
            return false;
        }

        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            self::update($id, [
                'status'        => 'archived',
                'archived_at'   => date('Y-m-d H:i:s'),
                'do_not_engage' => 1,
            ]);

            $stmt = $db->prepare("
                INSERT INTO outreach_do_not_engage
                    (tenant_id, subject_type, subject_urn, public_identifier, reason, created_by_user_id)
                VALUES (?, 'person', ?, ?, 'archived', ?)
                ON DUPLICATE KEY UPDATE reason = 'archived'
            ");
            $stmt->execute([
                $tenantId,
                $lead['member_urn'] ?: $lead['linkedin_id'],
                $lead['linkedin_username'],
                $userId,
            ]);

            $db->commit();
            return true;
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log('LinkedInLead::archive failed: ' . $e->getMessage());
            return false;
        }
    }

    public static function convertToCustomer($id, $tenantId) {
        $lead = self::find($id, $tenantId);
        if (!$lead) {
            return false;
        }

        // Create customer user record
        $customerId = User::create([
            'tenant_id' => $tenantId,
            'role'      => 'customer',
            'name'      => $lead['full_name'],
            'email'     => $lead['email'],
            'password'  => bin2hex(random_bytes(16)),
            'phone'     => $lead['phone'] ?? null,
            'company'   => $lead['company'] ?? null,
            'status'    => 'active',
        ]);

        self::update($id, [
            'status'       => 'converted',
            'customer_id'  => $customerId,
            'converted_at' => date('Y-m-d H:i:s'),
        ]);

        return $customerId;
    }
}
