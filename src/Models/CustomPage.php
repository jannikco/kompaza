<?php

namespace App\Models;

use App\Database\Database;

class CustomPage {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM custom_pages WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM custom_pages WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findBySlug($slug, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM custom_pages WHERE slug = ? AND tenant_id = ? AND status = 'published'");
        $stmt->execute([$slug, $tenantId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM custom_pages WHERE tenant_id = ? ORDER BY sort_order ASC, created_at DESC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function getHomepage($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM custom_pages WHERE tenant_id = ? AND is_homepage = 1 AND status = 'published' LIMIT 1");
        $stmt->execute([$tenantId]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO custom_pages (tenant_id, slug, title, content, layout, meta_description, status, is_homepage, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['slug'],
            $data['title'],
            $data['content'] ?? null,
            $data['layout'] ?? 'shop',
            $data['meta_description'] ?? null,
            $data['status'] ?? 'draft',
            $data['is_homepage'] ?? false,
            $data['sort_order'] ?? 0,
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
        $stmt = $db->prepare("UPDATE custom_pages SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM custom_pages WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function countByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM custom_pages WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['count'];
    }

    public static function clearHomepage($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE custom_pages SET is_homepage = 0 WHERE tenant_id = ?");
        return $stmt->execute([$tenantId]);
    }

    public static function incrementViews($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE custom_pages SET view_count = view_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
