<?php

namespace App\Models;

use App\Database\Database;

class Order {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findByOrderNumber($orderNumber, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? AND tenant_id = ?");
        $stmt->execute([$orderNumber, $tenantId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $status = null, $limit = 50, $offset = 0) {
        $db = Database::getConnection();
        $sql = "SELECT * FROM orders WHERE tenant_id = ?";
        $params = [$tenantId];

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function allByCustomer($customerId, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM orders WHERE customer_id = ? AND tenant_id = ? ORDER BY created_at DESC");
        $stmt->execute([$customerId, $tenantId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO orders (tenant_id, order_number, customer_id, customer_name, customer_email, customer_phone, customer_company, billing_address, shipping_address, subtotal_dkk, tax_dkk, shipping_dkk, discount_dkk, total_dkk, currency, payment_method, payment_reference, status, notes, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['order_number'],
            $data['customer_id'] ?? null,
            $data['customer_name'] ?? null,
            $data['customer_email'] ?? null,
            $data['customer_phone'] ?? null,
            $data['customer_company'] ?? null,
            $data['billing_address'] ?? null,
            $data['shipping_address'] ?? null,
            $data['subtotal_dkk'] ?? 0.00,
            $data['tax_dkk'] ?? 0.00,
            $data['shipping_dkk'] ?? 0.00,
            $data['discount_dkk'] ?? 0.00,
            $data['total_dkk'] ?? 0.00,
            $data['currency'] ?? 'DKK',
            $data['payment_method'] ?? null,
            $data['payment_reference'] ?? null,
            $data['status'] ?? 'pending',
            $data['notes'] ?? null,
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
        $stmt = $db->prepare("UPDATE orders SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function updateStatus($id, $status, $note = null) {
        $db = Database::getConnection();

        $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        // Insert into order_status_history
        $stmt = $db->prepare("
            INSERT INTO order_status_history (order_id, status, note, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$id, $status, $note]);

        return true;
    }

    public static function countByTenant($tenantId, $status = null) {
        $db = Database::getConnection();
        if ($status) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE tenant_id = ? AND status = ?");
            $stmt->execute([$tenantId, $status]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE tenant_id = ?");
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetch()['count'];
    }

    public static function recentByTenant($tenantId, $limit = 10) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM orders WHERE tenant_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$tenantId, $limit]);
        return $stmt->fetchAll();
    }

    public static function totalRevenueByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COALESCE(SUM(total_dkk), 0) as total FROM orders WHERE tenant_id = ? AND status NOT IN ('cancelled', 'refunded')");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['total'];
    }
}
