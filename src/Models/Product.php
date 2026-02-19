<?php

namespace App\Models;

use App\Database\Database;

class Product {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findBySlug($slug, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM products WHERE slug = ? AND tenant_id = ? AND status = 'published'");
        $stmt->execute([$slug, $tenantId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $status = null) {
        $db = Database::getConnection();
        if ($status) {
            $stmt = $db->prepare("SELECT * FROM products WHERE tenant_id = ? AND status = ? ORDER BY sort_order ASC, created_at DESC");
            $stmt->execute([$tenantId, $status]);
        } else {
            $stmt = $db->prepare("SELECT * FROM products WHERE tenant_id = ? ORDER BY sort_order ASC, created_at DESC");
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll();
    }

    public static function publishedByTenant($tenantId, $limit = 50) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM products WHERE tenant_id = ? AND status = 'published' ORDER BY sort_order ASC, created_at DESC LIMIT ?");
        $stmt->execute([$tenantId, $limit]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO products (tenant_id, slug, name, description, short_description, image_path, gallery, price_dkk, compare_price_dkk, sku, stock_quantity, track_stock, category, tags, is_digital, digital_file_path, status, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['slug'],
            $data['name'],
            $data['description'] ?? null,
            $data['short_description'] ?? null,
            $data['image_path'] ?? null,
            $data['gallery'] ?? null,
            $data['price_dkk'] ?? 0.00,
            $data['compare_price_dkk'] ?? null,
            $data['sku'] ?? null,
            $data['stock_quantity'] ?? 0,
            $data['track_stock'] ?? 0,
            $data['category'] ?? null,
            $data['tags'] ?? null,
            $data['is_digital'] ?? 0,
            $data['digital_file_path'] ?? null,
            $data['status'] ?? 'draft',
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
        $stmt = $db->prepare("UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM products WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function incrementViews($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE products SET view_count = view_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function countByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM products WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['count'];
    }
}
