<?php

namespace App\Models;

use App\Database\Database;

class LinkedInLead {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM linkedin_leads WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM linkedin_leads WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $search = null, $limit = 50, $offset = 0) {
        $db = Database::getConnection();
        $sql = "SELECT * FROM linkedin_leads WHERE tenant_id = ?";
        $params = [$tenantId];

        if ($search) {
            $sql .= " AND (full_name LIKE ? OR company LIKE ? OR job_title LIKE ? OR email LIKE ? OR linkedin_url LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function allByCampaign($campaignId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM linkedin_leads WHERE campaign_id = ? ORDER BY created_at DESC");
        $stmt->execute([$campaignId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO linkedin_leads (tenant_id, campaign_id, full_name, first_name, last_name, email, phone, company, job_title, linkedin_url, location, industry, connection_degree, notes, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['campaign_id'] ?? null,
            $data['full_name'],
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['company'] ?? null,
            $data['job_title'] ?? null,
            $data['linkedin_url'] ?? null,
            $data['location'] ?? null,
            $data['industry'] ?? null,
            $data['connection_degree'] ?? null,
            $data['notes'] ?? null,
            $data['status'] ?? 'new',
        ]);
        return $db->lastInsertId();
    }

    public static function update($id, $data) {
        $db = Database::getConnection();
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
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

    public static function convertToCustomer($id, $tenantId) {
        $db = Database::getConnection();
        $lead = self::find($id, $tenantId);
        if (!$lead) {
            return false;
        }

        // Create customer user record
        $customerId = User::create([
            'tenant_id' => $tenantId,
            'role' => 'customer',
            'name' => $lead['full_name'],
            'email' => $lead['email'],
            'password' => bin2hex(random_bytes(16)),
            'phone' => $lead['phone'] ?? null,
            'company' => $lead['company'] ?? null,
            'status' => 'active',
        ]);

        // Update lead status
        self::update($id, [
            'status' => 'converted',
            'converted_customer_id' => $customerId,
        ]);

        return $customerId;
    }
}
