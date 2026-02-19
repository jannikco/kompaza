<?php

namespace App\Models;

use App\Database\Database;

class EmailSignup {
    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO email_signups (tenant_id, email, name, source_type, source_id, source_slug, brevo_synced, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['email'],
            $data['name'] ?? null,
            $data['source_type'] ?? 'newsletter',
            $data['source_id'] ?? null,
            $data['source_slug'] ?? null,
            $data['brevo_synced'] ?? 0,
            $data['ip_address'] ?? null,
            $data['user_agent'] ?? null,
        ]);
        return $db->lastInsertId();
    }

    public static function allByTenant($tenantId, $limit = 50, $offset = 0) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM email_signups WHERE tenant_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$tenantId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    public static function countByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM email_signups WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['count'];
    }

    public static function findByEmail($email, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM email_signups WHERE email = ? AND tenant_id = ?");
        $stmt->execute([$email, $tenantId]);
        return $stmt->fetch();
    }

    public static function recentByTenant($tenantId, $limit = 10) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM email_signups WHERE tenant_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$tenantId, $limit]);
        return $stmt->fetchAll();
    }
}
