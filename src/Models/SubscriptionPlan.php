<?php

namespace App\Models;

use App\Database\Database;

class SubscriptionPlan {

    public static function find($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM subscription_plans WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function findBySlug($slug) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM subscription_plans WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public static function allActive() {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY display_order ASC");
        return $stmt->fetchAll();
    }

    public static function all() {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM subscription_plans ORDER BY display_order ASC");
        return $stmt->fetchAll();
    }

    public static function update($id, $data) {
        $db = Database::getConnection();
        $fields = [];
        $values = [];

        $allowedFields = [
            'name', 'slug', 'stripe_product_id', 'stripe_price_monthly_id', 'stripe_price_annual_id',
            'price_monthly_usd', 'price_annual_usd', 'max_customers', 'max_lead_magnets',
            'max_products', 'display_order', 'is_active'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) return 0;

        $values[] = $id;
        $sql = "UPDATE subscription_plans SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($values);
        return $stmt->rowCount();
    }
}
