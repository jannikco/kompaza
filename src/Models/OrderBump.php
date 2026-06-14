<?php

namespace App\Models;

use App\Database\Database;

class OrderBump {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM order_bumps WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM order_bumps WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $status = null) {
        $db = Database::getConnection();
        if ($status) {
            $stmt = $db->prepare("SELECT ob.*, p.name as product_name, p.price_dkk as product_price, p.image_path as product_image FROM order_bumps ob JOIN products p ON ob.product_id = p.id WHERE ob.tenant_id = ? AND ob.status = ? ORDER BY ob.sort_order ASC");
            $stmt->execute([$tenantId, $status]);
        } else {
            $stmt = $db->prepare("SELECT ob.*, p.name as product_name, p.price_dkk as product_price, p.image_path as product_image FROM order_bumps ob JOIN products p ON ob.product_id = p.id WHERE ob.tenant_id = ? ORDER BY ob.sort_order ASC");
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll();
    }

    /**
     * Get active bumps applicable to a set of cart product IDs.
     */
    public static function getApplicable($tenantId, array $cartProductIds = []) {
        $db = Database::getConnection();
        $bumps = [];

        $stmt = $db->prepare("
            SELECT ob.*, p.name as product_name, p.price_dkk as product_price, p.image_path as product_image, p.short_description as product_description
            FROM order_bumps ob
            JOIN products p ON ob.product_id = p.id
            WHERE ob.tenant_id = ? AND ob.status = 'active' AND p.status = 'published'
            ORDER BY ob.sort_order ASC
        ");
        $stmt->execute([$tenantId]);
        $allBumps = $stmt->fetchAll();

        foreach ($allBumps as $bump) {
            if ($bump['applies_to'] === 'all') {
                // Don't show bump if the bump product is already in cart
                if (!in_array($bump['product_id'], $cartProductIds)) {
                    $bumps[] = $bump;
                }
            } elseif ($bump['applies_to'] === 'specific_products') {
                $targetIds = json_decode($bump['applies_to_value'] ?? '[]', true) ?: [];
                if (array_intersect($cartProductIds, $targetIds)) {
                    if (!in_array($bump['product_id'], $cartProductIds)) {
                        $bumps[] = $bump;
                    }
                }
            }
        }

        return $bumps;
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO order_bumps (tenant_id, name, description, product_id, bump_price_dkk, display_text, applies_to, applies_to_value, sort_order, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['product_id'],
            $data['bump_price_dkk'],
            $data['display_text'] ?? null,
            $data['applies_to'] ?? 'all',
            $data['applies_to_value'] ?? null,
            $data['sort_order'] ?? 0,
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
        $stmt = $db->prepare("UPDATE order_bumps SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM order_bumps WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function incrementShown($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE order_bumps SET times_shown = times_shown + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function incrementAccepted($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE order_bumps SET times_accepted = times_accepted + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
