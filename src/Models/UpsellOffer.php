<?php

namespace App\Models;

use App\Database\Database;

class UpsellOffer {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM upsell_offers WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM upsell_offers WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $type = null) {
        $db = Database::getConnection();
        if ($type) {
            $stmt = $db->prepare("SELECT uo.*, p.name as product_name, p.price_dkk as product_price, p.image_path as product_image FROM upsell_offers uo JOIN products p ON uo.product_id = p.id WHERE uo.tenant_id = ? AND uo.offer_type = ? ORDER BY uo.sort_order ASC");
            $stmt->execute([$tenantId, $type]);
        } else {
            $stmt = $db->prepare("SELECT uo.*, p.name as product_name, p.price_dkk as product_price, p.image_path as product_image FROM upsell_offers uo JOIN products p ON uo.product_id = p.id WHERE uo.tenant_id = ? ORDER BY uo.offer_type ASC, uo.sort_order ASC");
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll();
    }

    /**
     * Find the first matching upsell for products just purchased.
     */
    public static function findForPurchase($tenantId, array $purchasedProductIds) {
        $db = Database::getConnection();

        // First try to find upsells that match specific trigger products
        $stmt = $db->prepare("
            SELECT uo.*, p.name as product_name, p.price_dkk as product_price, p.image_path as product_image, p.short_description as product_description
            FROM upsell_offers uo
            JOIN products p ON uo.product_id = p.id
            WHERE uo.tenant_id = ? AND uo.status = 'active' AND uo.offer_type = 'upsell' AND p.status = 'published'
            ORDER BY uo.sort_order ASC
        ");
        $stmt->execute([$tenantId]);
        $upsells = $stmt->fetchAll();

        foreach ($upsells as $upsell) {
            // Don't offer a product they just bought
            if (in_array($upsell['product_id'], $purchasedProductIds)) {
                continue;
            }

            $triggerIds = json_decode($upsell['trigger_product_ids'] ?? 'null', true);
            if ($triggerIds === null) {
                // null means any purchase triggers it
                return $upsell;
            }
            if (array_intersect($purchasedProductIds, $triggerIds)) {
                return $upsell;
            }
        }

        return null;
    }

    /**
     * Find the downsell associated with a given upsell.
     */
    public static function findDownsell($upsellId, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT uo.*, p.name as product_name, p.price_dkk as product_price, p.image_path as product_image, p.short_description as product_description
            FROM upsell_offers uo
            JOIN products p ON uo.product_id = p.id
            WHERE uo.parent_upsell_id = ? AND uo.tenant_id = ? AND uo.status = 'active' AND uo.offer_type = 'downsell' AND p.status = 'published'
            LIMIT 1
        ");
        $stmt->execute([$upsellId, $tenantId]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO upsell_offers (tenant_id, name, headline, description, product_id, offer_price_dkk, original_price_dkk, trigger_product_ids, offer_type, parent_upsell_id, button_text, decline_text, image_path, sort_order, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['name'],
            $data['headline'] ?? null,
            $data['description'] ?? null,
            $data['product_id'],
            $data['offer_price_dkk'],
            $data['original_price_dkk'] ?? null,
            $data['trigger_product_ids'] ?? null,
            $data['offer_type'] ?? 'upsell',
            $data['parent_upsell_id'] ?? null,
            $data['button_text'] ?? 'Yes, Add This To My Order!',
            $data['decline_text'] ?? 'No thanks, I\'ll pass',
            $data['image_path'] ?? null,
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
        $stmt = $db->prepare("UPDATE upsell_offers SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM upsell_offers WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function incrementShown($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE upsell_offers SET times_shown = times_shown + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function incrementAccepted($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE upsell_offers SET times_accepted = times_accepted + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
