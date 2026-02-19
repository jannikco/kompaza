<?php

namespace App\Models;

use App\Database\Database;

class Campaign {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM leadshark_campaigns WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM leadshark_campaigns WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM leadshark_campaigns WHERE tenant_id = ? ORDER BY created_at DESC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO leadshark_campaigns (tenant_id, name, description, linkedin_search_url, status, target_count, leads_collected, leads_contacted, leads_responded, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['linkedin_search_url'] ?? null,
            $data['status'] ?? 'draft',
            $data['target_count'] ?? 0,
            $data['leads_collected'] ?? 0,
            $data['leads_contacted'] ?? 0,
            $data['leads_responded'] ?? 0,
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
        $stmt = $db->prepare("UPDATE leadshark_campaigns SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM leadshark_campaigns WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function countByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM leadshark_campaigns WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['count'];
    }

    public static function updateStats($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE leadshark_campaigns SET
                leads_collected = (SELECT COUNT(*) FROM linkedin_leads WHERE campaign_id = ?),
                leads_contacted = (SELECT COUNT(*) FROM linkedin_leads WHERE campaign_id = ? AND status IN ('contacted', 'responded', 'converted')),
                leads_responded = (SELECT COUNT(*) FROM linkedin_leads WHERE campaign_id = ? AND status IN ('responded', 'converted'))
            WHERE id = ?
        ");
        return $stmt->execute([$id, $id, $id, $id]);
    }
}
