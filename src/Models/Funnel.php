<?php

namespace App\Models;

use App\Database\Database;

class Funnel {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM funnels WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM funnels WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findBySlug($slug, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM funnels WHERE slug = ? AND tenant_id = ?");
        $stmt->execute([$slug, $tenantId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $status = null) {
        $db = Database::getConnection();
        if ($status) {
            $stmt = $db->prepare("SELECT * FROM funnels WHERE tenant_id = ? AND status = ? ORDER BY created_at DESC");
            $stmt->execute([$tenantId, $status]);
        } else {
            $stmt = $db->prepare("SELECT * FROM funnels WHERE tenant_id = ? ORDER BY created_at DESC");
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO funnels (tenant_id, name, slug, funnel_type, description, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['name'],
            $data['slug'],
            $data['funnel_type'] ?? 'sales',
            $data['description'] ?? null,
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
        $stmt = $db->prepare("UPDATE funnels SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM funnels WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function incrementViews($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE funnels SET total_views = total_views + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function incrementConversions($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE funnels SET total_conversions = total_conversions + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function countByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM funnels WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['count'];
    }

    public static function getWithStats($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT f.*,
                   COUNT(fs.id) as step_count,
                   CASE WHEN f.total_views > 0 THEN ROUND(f.total_conversions / f.total_views * 100, 1) ELSE 0 END as conversion_rate
            FROM funnels f
            LEFT JOIN funnel_steps fs ON f.id = fs.funnel_id
            WHERE f.tenant_id = ?
            GROUP BY f.id
            ORDER BY f.created_at DESC
        ");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }
}
