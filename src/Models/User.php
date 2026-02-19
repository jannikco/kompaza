<?php

namespace App\Models;

use App\Database\Database;

class User {
    public static function find($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function findByEmail($email, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId === null) {
            // Superadmin lookup (no tenant)
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND tenant_id IS NULL");
            $stmt->execute([$email]);
        } else {
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND tenant_id = ?");
            $stmt->execute([$email, $tenantId]);
        }
        return $stmt->fetch();
    }

    public static function findByEmailGlobal($email) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $role = null) {
        $db = Database::getConnection();
        if ($role) {
            $stmt = $db->prepare("SELECT * FROM users WHERE tenant_id = ? AND role = ? ORDER BY created_at DESC");
            $stmt->execute([$tenantId, $role]);
        } else {
            $stmt = $db->prepare("SELECT * FROM users WHERE tenant_id = ? ORDER BY created_at DESC");
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll();
    }

    public static function customersByTenant($tenantId, $search = null, $limit = 50, $offset = 0) {
        $db = Database::getConnection();
        $sql = "SELECT * FROM users WHERE tenant_id = ? AND role = 'customer'";
        $params = [$tenantId];

        if ($search) {
            $sql .= " AND (name LIKE ? OR email LIKE ? OR company LIKE ?)";
            $searchTerm = '%' . $search . '%';
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

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO users (tenant_id, role, name, email, password_hash, phone, company, address_line1, address_line2, postal_code, city, country, cvr_number, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['tenant_id'] ?? null,
            $data['role'],
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['phone'] ?? null,
            $data['company'] ?? null,
            $data['address_line1'] ?? null,
            $data['address_line2'] ?? null,
            $data['postal_code'] ?? null,
            $data['city'] ?? null,
            $data['country'] ?? 'DK',
            $data['cvr_number'] ?? null,
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
        $stmt = $db->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function updatePassword($id, $newPassword) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        return $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);
    }

    public static function updateLastLogin($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function delete($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function countByTenant($tenantId, $role = null) {
        $db = Database::getConnection();
        if ($role) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE tenant_id = ? AND role = ?");
            $stmt->execute([$tenantId, $role]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE tenant_id = ?");
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetch()['count'];
    }

    public static function emailExistsForTenant($email, $tenantId, $excludeId = null) {
        $db = Database::getConnection();
        if ($excludeId) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE email = ? AND tenant_id = ? AND id != ?");
            $stmt->execute([$email, $tenantId, $excludeId]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE email = ? AND tenant_id = ?");
            $stmt->execute([$email, $tenantId]);
        }
        return $stmt->fetch()['count'] > 0;
    }
}
