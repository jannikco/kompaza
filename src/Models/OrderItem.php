<?php

namespace App\Models;

use App\Database\Database;

class OrderItem {
    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, product_sku, quantity, unit_price_dkk, total_price_dkk, is_digital, digital_file_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['order_id'],
            $data['product_id'] ?? null,
            $data['product_name'],
            $data['product_sku'] ?? null,
            $data['quantity'] ?? 1,
            $data['unit_price_dkk'] ?? 0.00,
            $data['total_price_dkk'] ?? 0.00,
            $data['is_digital'] ?? 0,
            $data['digital_file_path'] ?? null,
        ]);
        return $db->lastInsertId();
    }

    public static function allByOrder($orderId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public static function deleteByOrder($orderId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM order_items WHERE order_id = ?");
        return $stmt->execute([$orderId]);
    }
}
