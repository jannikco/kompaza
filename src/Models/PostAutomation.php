<?php

namespace App\Models;

use App\Database\Database;
use App\Services\Outreach\KeywordMatcher;

class PostAutomation {
    private const COLUMNS = [
        'name', 'linkedin_account_id', 'post_url', 'post_urn', 'trigger_keyword',
        'trigger_keywords', 'auto_reply_enabled', 'auto_reply_template',
        'non_first_degree_reply_template', 'auto_dm_enabled', 'dm_template',
        'dm_templates', 'lead_magnet_id', 'status', 'auto_like_enabled',
        'auto_connect_enabled', 'partially_engage', 'follow_up_enabled',
        'follow_up_template', 'follow_up_delay_minutes', 'follow_up_only_if_no_response',
    ];

    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM connectpilot_post_automations WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM connectpilot_post_automations WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM connectpilot_post_automations WHERE tenant_id = ? ORDER BY created_at DESC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $keywords = KeywordMatcher::normalize($data['trigger_keywords'] ?? $data['trigger_keyword'] ?? []);
        $triggerKeyword = $keywords[0] ?? (string) ($data['trigger_keyword'] ?? '');
        $dmTemplates = $data['dm_templates'] ?? null;
        if (is_array($dmTemplates)) {
            $dmTemplates = json_encode(array_values($dmTemplates), JSON_UNESCAPED_UNICODE);
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO connectpilot_post_automations
                (tenant_id, linkedin_account_id, name, post_url, post_urn, trigger_keyword, trigger_keywords,
                 auto_reply_enabled, auto_reply_template, non_first_degree_reply_template,
                 auto_dm_enabled, dm_template, dm_templates, lead_magnet_id, status,
                 auto_like_enabled, auto_connect_enabled, partially_engage,
                 follow_up_enabled, follow_up_template, follow_up_delay_minutes, follow_up_only_if_no_response,
                 created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['linkedin_account_id'],
            $data['name'],
            $data['post_url'],
            $data['post_urn'] ?? null,
            $triggerKeyword,
            $keywords !== [] ? json_encode($keywords, JSON_UNESCAPED_UNICODE) : null,
            $data['auto_reply_enabled'] ?? 1,
            $data['auto_reply_template'] ?? null,
            $data['non_first_degree_reply_template'] ?? null,
            $data['auto_dm_enabled'] ?? 1,
            $data['dm_template'] ?? null,
            $dmTemplates,
            $data['lead_magnet_id'] ?? null,
            $data['status'] ?? 'draft',
            $data['auto_like_enabled'] ?? 0,
            $data['auto_connect_enabled'] ?? 0,
            $data['partially_engage'] ?? 0,
            $data['follow_up_enabled'] ?? 0,
            $data['follow_up_template'] ?? null,
            $data['follow_up_delay_minutes'] ?? 120,
            $data['follow_up_only_if_no_response'] ?? 1,
        ]);
        return $db->lastInsertId();
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
        if ($fields === []) {
            return false;
        }
        $values[] = $id;
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE connectpilot_post_automations SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM connectpilot_post_automations WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function activeWithAccounts() {
        $db = Database::getConnection();
        // Credentials are NOT selected here. They live encrypted and are read through
        // LinkedInAccount::session(), which is the only place that decrypts.
        //
        // The "has a session" test must accept both shapes: encryption_version >= 1 for
        // encrypted rows (where li_at_cookie is deliberately NULL) and the legacy
        // plaintext column for rows not yet migrated. Testing only the old column would
        // silently return zero automations for every encrypted account.
        $stmt = $db->prepare("
            SELECT pa.*, la.daily_message_limit, la.messages_sent_today,
                   la.daily_connection_limit, la.connections_sent_today,
                   la.profile_urn, la.timezone, la.status AS account_status,
                   la.emergency_stop, la.safety_mode, la.respect_off_hours,
                   la.working_hours_json, la.id AS account_id
            FROM connectpilot_post_automations pa
            JOIN linkedin_accounts la ON la.id = pa.linkedin_account_id
            WHERE pa.status = 'active'
              AND la.status = 'active'
              AND (la.encryption_version >= 1 OR la.li_at_cookie IS NOT NULL)
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function incrementStats($id, $field) {
        $allowed = [
            'comments_detected', 'keyword_matches', 'replies_sent', 'dms_sent',
            'leads_captured', 'likes_sent', 'connections_sent', 'follow_ups_sent',
        ];
        if (!in_array($field, $allowed)) return false;

        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE connectpilot_post_automations SET {$field} = {$field} + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function updateLastChecked($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE connectpilot_post_automations SET last_checked_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function countActiveByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM connectpilot_post_automations WHERE tenant_id = ? AND status = 'active'");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['count'];
    }

    public static function totalDMsSentByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COALESCE(SUM(dms_sent), 0) as total FROM connectpilot_post_automations WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['total'];
    }

    public static function recentByTenant($tenantId, $limit = 5) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM connectpilot_post_automations WHERE tenant_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$tenantId, $limit]);
        return $stmt->fetchAll();
    }

    /** @return list<string> */
    /**
     * Normalize an admin form POST into columns `create()` / `update()` accept.
     *
     * @param array<string,mixed> $post
     * @return array<string,mixed>
     */
    public static function fromForm(array $post): array
    {
        $keywords = KeywordMatcher::normalize($post['trigger_keywords'] ?? $post['trigger_keyword'] ?? '');
        $status = (string) ($post['status'] ?? 'draft');
        if (!in_array($status, ['draft', 'active', 'paused', 'completed'], true)) {
            $status = 'draft';
        }
        $delay = max(1, min(10080, (int) ($post['follow_up_delay_minutes'] ?? 120)));

        return [
            'name'                              => trim((string) ($post['name'] ?? '')),
            'linkedin_account_id'               => !empty($post['linkedin_account_id']) ? (int) $post['linkedin_account_id'] : null,
            'post_url'                          => trim((string) ($post['post_url'] ?? '')),
            'trigger_keyword'                   => $keywords[0] ?? '',
            'trigger_keywords'                  => $keywords !== [] ? json_encode($keywords, JSON_UNESCAPED_UNICODE) : null,
            'status'                            => $status,
            'auto_reply_enabled'                => isset($post['auto_reply_enabled']) ? 1 : 0,
            'auto_reply_template'               => strip_tags((string) ($post['auto_reply_template'] ?? '')),
            'non_first_degree_reply_template'   => strip_tags((string) ($post['non_first_degree_reply_template'] ?? '')),
            'auto_dm_enabled'                   => isset($post['auto_dm_enabled']) ? 1 : 0,
            'dm_template'                       => strip_tags((string) ($post['dm_template'] ?? '')),
            'lead_magnet_id'                    => !empty($post['lead_magnet_id']) ? (int) $post['lead_magnet_id'] : null,
            'auto_like_enabled'                 => isset($post['auto_like_enabled']) ? 1 : 0,
            'auto_connect_enabled'              => isset($post['auto_connect_enabled']) ? 1 : 0,
            'partially_engage'                  => isset($post['partially_engage']) ? 1 : 0,
            'follow_up_enabled'                 => isset($post['follow_up_enabled']) ? 1 : 0,
            'follow_up_template'                => strip_tags((string) ($post['follow_up_template'] ?? '')),
            'follow_up_delay_minutes'           => $delay,
            'follow_up_only_if_no_response'     => isset($post['follow_up_only_if_no_response']) ? 1 : 0,
        ];
    }

    /** Display helper: comma-separated keywords for the form. */
    public static function keywordsCsv(array $row): string
    {
        return implode(', ', self::keywords($row));
    }

    public static function keywords(array $row): array
    {
        $fromJson = KeywordMatcher::normalize($row['trigger_keywords'] ?? null);
        if ($fromJson !== []) {
            return $fromJson;
        }
        return KeywordMatcher::normalize($row['trigger_keyword'] ?? null);
    }

    public static function pickDmTemplate(array $row, int $ordinal = 0): string
    {
        $list = $row['dm_templates'] ?? null;
        if (is_string($list) && $list !== '') {
            $decoded = json_decode($list, true);
            $list = is_array($decoded) ? $decoded : null;
        }
        if (is_array($list) && $list !== []) {
            $templates = array_values(array_filter($list, static fn ($t) => is_string($t) && trim($t) !== ''));
            if ($templates !== []) {
                return $templates[$ordinal % count($templates)];
            }
        }
        return (string) ($row['dm_template'] ?? '');
    }
}
