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
            INSERT INTO leadshark_campaigns (tenant_id, name, description, linkedin_account_id, search_url, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['linkedin_account_id'] ?? null,
            $data['search_url'] ?? null,
            $data['status'] ?? 'draft',
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
                leads_found = (SELECT COUNT(*) FROM linkedin_leads WHERE campaign_id = ?),
                connections_sent = (SELECT COUNT(*) FROM linkedin_leads WHERE campaign_id = ? AND connection_status IN ('pending', 'accepted')),
                replies_received = (SELECT COUNT(*) FROM linkedin_leads WHERE campaign_id = ? AND last_replied_at IS NOT NULL)
            WHERE id = ?
        ");
        return $stmt->execute([$id, $id, $id, $id]);
    }
}
