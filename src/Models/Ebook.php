<?php

namespace App\Models;

use App\Database\Database;

class Ebook {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM ebooks WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM ebooks WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findBySlug($slug, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM ebooks WHERE slug = ? AND tenant_id = ? AND status = 'published'");
        $stmt->execute([$slug, $tenantId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $status = null) {
        $db = Database::getConnection();
        if ($status) {
            $stmt = $db->prepare("SELECT * FROM ebooks WHERE tenant_id = ? AND status = ? ORDER BY created_at DESC");
            $stmt->execute([$tenantId, $status]);
        } else {
            $stmt = $db->prepare("SELECT * FROM ebooks WHERE tenant_id = ? ORDER BY created_at DESC");
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll();
    }

    public static function publishedByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM ebooks WHERE tenant_id = ? AND status = 'published' ORDER BY created_at DESC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO ebooks (tenant_id, slug, title, subtitle, description, hero_headline, hero_subheadline, hero_bg_color, hero_cta_text, features_headline, key_metrics, cover_image_path, features, chapters, target_audience, testimonials, faq, pdf_filename, pdf_original_name, page_count, price_dkk, meta_description, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['slug'],
            $data['title'],
            $data['subtitle'] ?? null,
            $data['description'] ?? null,
            $data['hero_headline'] ?? null,
            $data['hero_subheadline'] ?? null,
            $data['hero_bg_color'] ?? null,
            $data['hero_cta_text'] ?? null,
            $data['features_headline'] ?? null,
            $data['key_metrics'] ?? null,
            $data['cover_image_path'] ?? null,
            $data['features'] ?? null,
            $data['chapters'] ?? null,
            $data['target_audience'] ?? null,
            $data['testimonials'] ?? null,
            $data['faq'] ?? null,
            $data['pdf_filename'] ?? null,
            $data['pdf_original_name'] ?? null,
            $data['page_count'] ?? null,
            $data['price_dkk'] ?? 0.00,
            $data['meta_description'] ?? null,
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
        $stmt = $db->prepare("UPDATE ebooks SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM ebooks WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function incrementViews($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE ebooks SET view_count = view_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function countByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM ebooks WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['count'];
    }
}
