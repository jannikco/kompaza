<?php

namespace App\Models;

use App\Database\Database;

class Tenant {
    public static function find($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM tenants WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function findByUuid($uuid) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM tenants WHERE uuid = ?");
        $stmt->execute([$uuid]);
        return $stmt->fetch();
    }

    public static function findBySlug($slug) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM tenants WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public static function all($status = null) {
        $db = Database::getConnection();
        if ($status) {
            $stmt = $db->prepare("SELECT * FROM tenants WHERE status = ? ORDER BY created_at DESC");
            $stmt->execute([$status]);
        } else {
            $stmt = $db->prepare("SELECT * FROM tenants ORDER BY created_at DESC");
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO tenants (uuid, name, slug, status, primary_color, secondary_color, company_name, email, currency, tax_rate, plan_id, trial_ends_at, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['uuid'] ?? generateUuid(),
            $data['name'],
            $data['slug'],
            $data['status'] ?? 'trial',
            $data['primary_color'] ?? '#3b82f6',
            $data['secondary_color'] ?? '#6366f1',
            $data['company_name'] ?? $data['name'],
            $data['email'] ?? null,
            $data['currency'] ?? 'DKK',
            $data['tax_rate'] ?? 25.00,
            $data['plan_id'] ?? null,
            $data['trial_ends_at'] ?? date('Y-m-d H:i:s', strtotime('+14 days')),
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
        $stmt = $db->prepare("UPDATE tenants SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function count($status = null) {
        $db = Database::getConnection();
        if ($status) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM tenants WHERE status = ?");
            $stmt->execute([$status]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM tenants");
            $stmt->execute();
        }
        return $stmt->fetch()['count'];
    }

    public static function slugExists($slug, $excludeId = null) {
        $db = Database::getConnection();
        if ($excludeId) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM tenants WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM tenants WHERE slug = ?");
            $stmt->execute([$slug]);
        }
        return $stmt->fetch()['count'] > 0;
    }
}
