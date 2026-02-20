<?php

namespace App\Models;

use App\Database\Database;

class Plan {
    public static function find($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM plans WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function findBySlug($slug) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM plans WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public static function allActive() {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order, price_monthly_usd");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function all() {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM plans ORDER BY sort_order, price_monthly_usd");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO plans (name, slug, price_monthly_usd, price_yearly_usd, max_customers, max_leads, max_campaigns, max_products, max_lead_magnets, features_json, is_active, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['price_monthly_usd'],
            $data['price_yearly_usd'] ?? null,
            $data['max_customers'] ?? null,
            $data['max_leads'] ?? null,
            $data['max_campaigns'] ?? null,
            $data['max_products'] ?? null,
            $data['max_lead_magnets'] ?? null,
            $data['features_json'] ?? null,
            $data['is_active'] ?? 1,
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
        $stmt = $db->prepare("UPDATE plans SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }
}
