<?php

namespace App\Models;

use App\Database\Database;

class DiscountCode {
    public static function find($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM discount_codes WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$id, $tenantId]);
        return $stmt->fetch();
    }

    public static function findByCode($code, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM discount_codes WHERE code = ? AND tenant_id = ?");
        $stmt->execute([strtoupper(trim($code)), $tenantId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM discount_codes WHERE tenant_id = ? ORDER BY created_at DESC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO discount_codes (tenant_id, code, type, value, min_order_dkk, max_uses, applies_to, expires_at, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            strtoupper(trim($data['code'])),
            $data['type'] ?? 'percentage',
            $data['value'],
            $data['min_order_dkk'] ?? null,
            $data['max_uses'] ?? null,
            $data['applies_to'] ?? 'all',
            $data['expires_at'] ?? null,
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
        $stmt = $db->prepare("UPDATE discount_codes SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM discount_codes WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function validate($code, $tenantId, $orderTotal = 0) {
        $discount = self::findByCode($code, $tenantId);
        if (!$discount) {
            return ['valid' => false, 'error' => 'Invalid discount code.'];
        }
        if ($discount['status'] !== 'active') {
            return ['valid' => false, 'error' => 'This discount code is no longer active.'];
        }
        if ($discount['expires_at'] && strtotime($discount['expires_at']) < time()) {
            return ['valid' => false, 'error' => 'This discount code has expired.'];
        }
        if ($discount['max_uses'] !== null && $discount['used_count'] >= $discount['max_uses']) {
            return ['valid' => false, 'error' => 'This discount code has reached its usage limit.'];
        }
        if ($discount['min_order_dkk'] && $orderTotal < (float)$discount['min_order_dkk']) {
            return ['valid' => false, 'error' => 'Minimum order amount of ' . number_format($discount['min_order_dkk'], 2) . ' DKK required.'];
        }

        // Calculate discount amount
        $discountAmount = 0;
        if ($discount['type'] === 'percentage') {
            $discountAmount = round($orderTotal * ($discount['value'] / 100), 2);
        } else {
            $discountAmount = min((float)$discount['value'], $orderTotal);
        }

        return [
            'valid' => true,
            'discount' => $discount,
            'discount_amount' => $discountAmount,
        ];
    }

    public static function incrementUsage($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE discount_codes SET used_count = used_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function recordUse($discountCodeId, $orderId, $userId, $amountDkk) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO discount_code_uses (discount_code_id, order_id, user_id, amount_dkk) VALUES (?, ?, ?, ?)");
        $stmt->execute([$discountCodeId, $orderId, $userId, $amountDkk]);
        self::incrementUsage($discountCodeId);
    }
}
