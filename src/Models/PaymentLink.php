<?php

namespace App\Models;

use App\Database\Database;

class PaymentLink {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM payment_links WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM payment_links WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findByToken($token) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT pl.*, p.name as product_name, p.price_dkk as product_price, p.image_path as product_image,
                   p.short_description as product_description, p.status as product_status,
                   t.slug as tenant_slug, t.company_name as tenant_name
            FROM payment_links pl
            JOIN products p ON pl.product_id = p.id
            JOIN tenants t ON pl.tenant_id = t.id
            WHERE pl.token = ? AND pl.status = 'active'
        ");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT pl.*, p.name as product_name, p.price_dkk as product_price
            FROM payment_links pl
            LEFT JOIN products p ON pl.product_id = p.id
            WHERE pl.tenant_id = ?
            ORDER BY pl.created_at DESC
        ");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $token = bin2hex(random_bytes(16));
        $stmt = $db->prepare("
            INSERT INTO payment_links (tenant_id, token, name, product_id, custom_price_dkk, custom_name, allow_quantity, max_uses, expires_at, redirect_url, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $token,
            $data['name'],
            $data['product_id'],
            $data['custom_price_dkk'] ?? null,
            $data['custom_name'] ?? null,
            $data['allow_quantity'] ?? 0,
            $data['max_uses'] ?? null,
            $data['expires_at'] ?? null,
            $data['redirect_url'] ?? null,
            $data['status'] ?? 'active',
        ]);
        return $db->lastInsertId();
    }

    public static function update($id, $data, $tenantId = null) {
        $db = Database::getConnection();
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $where = "id = ?";
        if ($tenantId !== null) {
            $where .= " AND tenant_id = ?";
            $values[] = $tenantId;
        }
        $stmt = $db->prepare("UPDATE payment_links SET " . implode(', ', $fields) . " WHERE " . $where);
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM payment_links WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function incrementUsed($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE payment_links SET used_count = used_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function isValid($link) {
        if ($link['status'] !== 'active') return false;
        if ($link['max_uses'] && $link['used_count'] >= $link['max_uses']) return false;
        if ($link['expires_at'] && strtotime($link['expires_at']) < time()) return false;
        if ($link['product_status'] !== 'published') return false;
        return true;
    }
}
