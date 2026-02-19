<?php

namespace App\Models;

use App\Database\Database;

class Setting {
    public static function get($key, $tenantId = null, $default = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ? AND tenant_id = ?");
            $stmt->execute([$key, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ? AND tenant_id IS NULL");
            $stmt->execute([$key]);
        }
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    }

    public static function set($key, $value, $tenantId = null, $type = 'text', $description = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO settings (tenant_id, setting_key, setting_value, setting_type, description)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), setting_type = VALUES(setting_type)
        ");
        return $stmt->execute([$tenantId, $key, $value, $type, $description]);
    }

    public static function allForTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM settings WHERE tenant_id = ? ORDER BY setting_key");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function allGlobal() {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM settings WHERE tenant_id IS NULL ORDER BY setting_key");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function delete($key, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("DELETE FROM settings WHERE setting_key = ? AND tenant_id = ?");
            return $stmt->execute([$key, $tenantId]);
        } else {
            $stmt = $db->prepare("DELETE FROM settings WHERE setting_key = ? AND tenant_id IS NULL");
            return $stmt->execute([$key]);
        }
    }
}
