<?php

namespace App\Models;

use App\Database\Database;

class Article {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM articles WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM articles WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findBySlug($slug, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM articles WHERE slug = ? AND tenant_id = ? AND status = 'published'");
        $stmt->execute([$slug, $tenantId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $status = null) {
        $db = Database::getConnection();
        if ($status) {
            $stmt = $db->prepare("SELECT * FROM articles WHERE tenant_id = ? AND status = ? ORDER BY published_at DESC, created_at DESC");
            $stmt->execute([$tenantId, $status]);
        } else {
            $stmt = $db->prepare("SELECT * FROM articles WHERE tenant_id = ? ORDER BY created_at DESC");
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll();
    }

    public static function publishedByTenant($tenantId, $limit = 20) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM articles WHERE tenant_id = ? AND status = 'published' ORDER BY published_at DESC LIMIT ?");
        $stmt->execute([$tenantId, $limit]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO articles (tenant_id, slug, title, excerpt, content, featured_image, meta_description, category, tags, status, published_at, author_name)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['slug'],
            $data['title'],
            $data['excerpt'] ?? null,
            $data['content'] ?? null,
            $data['featured_image'] ?? null,
            $data['meta_description'] ?? null,
            $data['category'] ?? null,
            $data['tags'] ?? null,
            $data['status'] ?? 'draft',
            $data['published_at'] ?? null,
            $data['author_name'] ?? null,
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
        $stmt = $db->prepare("UPDATE articles SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM articles WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function incrementViews($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE articles SET view_count = view_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function countByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM articles WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['count'];
    }
}
