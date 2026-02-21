<?php

namespace App\Models;

use App\Database\Database;

class Redirect {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM redirects WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM redirects WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findByPath($path, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM redirects WHERE from_path = ? AND tenant_id = ? AND is_active = 1");
        $stmt->execute([$path, $tenantId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM redirects WHERE tenant_id = ? ORDER BY hit_count DESC, created_at DESC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO redirects (tenant_id, from_path, to_path, status_code, is_active)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['from_path'],
            $data['to_path'],
            $data['status_code'] ?? 301,
            $data['is_active'] ?? 1,
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
        $stmt = $db->prepare("UPDATE redirects SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM redirects WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function incrementHits($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE redirects SET hit_count = hit_count + 1, last_hit_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
