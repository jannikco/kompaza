<?php

namespace App\Models;

use App\Database\Database;

class TenantDomain {
    public static function find($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM tenant_domains WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM tenant_domains WHERE tenant_id = ? ORDER BY is_primary DESC, created_at ASC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO tenant_domains (tenant_id, domain, is_primary, is_verified, verified_at, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['domain'],
            $data['is_primary'] ?? 0,
            $data['is_verified'] ?? 0,
            $data['verified_at'] ?? null,
        ]);
        return $db->lastInsertId();
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM tenant_domains WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function findByDomain($domain) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM tenant_domains WHERE domain = ?");
        $stmt->execute([$domain]);
        return $stmt->fetch();
    }
}
