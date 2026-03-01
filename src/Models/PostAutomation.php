<?php

namespace App\Models;

use App\Database\Database;

class PostAutomation {
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
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO connectpilot_post_automations (tenant_id, linkedin_account_id, name, post_url, post_urn, trigger_keyword, auto_reply_enabled, auto_reply_template, auto_dm_enabled, dm_template, lead_magnet_id, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['linkedin_account_id'],
            $data['name'],
            $data['post_url'],
            $data['post_urn'] ?? null,
            $data['trigger_keyword'],
            $data['auto_reply_enabled'] ?? 1,
            $data['auto_reply_template'] ?? null,
            $data['auto_dm_enabled'] ?? 1,
            $data['dm_template'] ?? null,
            $data['lead_magnet_id'] ?? null,
            $data['status'] ?? 'active',
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
        $stmt = $db->prepare("
            SELECT pa.*, la.li_at_cookie, la.csrf_token, la.daily_message_limit,
                   la.messages_sent_today, la.id as account_id
            FROM connectpilot_post_automations pa
            JOIN linkedin_accounts la ON la.id = pa.linkedin_account_id
            WHERE pa.status = 'active'
            AND la.status = 'active'
            AND la.li_at_cookie IS NOT NULL
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function incrementStats($id, $field) {
        $allowed = ['comments_detected', 'keyword_matches', 'replies_sent', 'dms_sent', 'leads_captured'];
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
}
