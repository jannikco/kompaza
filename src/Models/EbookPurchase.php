<?php

namespace App\Models;

use App\Database\Database;

class EbookPurchase {

    public static function find($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM ebook_purchases WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function findByCheckoutSession($sessionId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM ebook_purchases WHERE stripe_checkout_session_id = ?");
        $stmt->execute([$sessionId]);
        return $stmt->fetch();
    }

    public static function findByPaymentIntent($paymentIntentId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM ebook_purchases WHERE stripe_payment_intent_id = ?");
        $stmt->execute([$paymentIntentId]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO ebook_purchases (tenant_id, ebook_id, customer_email, customer_name,
                stripe_checkout_session_id, stripe_payment_intent_id, amount_cents, currency,
                application_fee_cents, status, download_token_id, completed_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['ebook_id'],
            $data['customer_email'] ?? null,
            $data['customer_name'] ?? null,
            $data['stripe_checkout_session_id'] ?? null,
            $data['stripe_payment_intent_id'] ?? null,
            $data['amount_cents'] ?? 0,
            $data['currency'] ?? 'dkk',
            $data['application_fee_cents'] ?? 0,
            $data['status'] ?? 'pending',
            $data['download_token_id'] ?? null,
            $data['completed_at'] ?? null,
        ]);
        return $db->lastInsertId();
    }

    public static function update($id, $data) {
        $db = Database::getConnection();
        $fields = [];
        $values = [];

        $allowedFields = [
            'customer_email', 'customer_name', 'stripe_payment_intent_id',
            'amount_cents', 'currency', 'application_fee_cents', 'status',
            'download_token_id', 'completed_at'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) return 0;

        $values[] = $id;
        $sql = "UPDATE ebook_purchases SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($values);
        return $stmt->rowCount();
    }

    public static function allByTenantId($tenantId, $limit = 50) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT ep.*, e.title as ebook_title, e.slug as ebook_slug
            FROM ebook_purchases ep
            LEFT JOIN ebooks e ON ep.ebook_id = e.id
            WHERE ep.tenant_id = ?
            ORDER BY ep.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$tenantId, $limit]);
        return $stmt->fetchAll();
    }

    public static function countByTenantId($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM ebook_purchases WHERE tenant_id = ? AND status = 'completed'");
        $stmt->execute([$tenantId]);
        return (int)$stmt->fetchColumn();
    }

    public static function revenueByTenantId($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT SUM(amount_cents) as total FROM ebook_purchases WHERE tenant_id = ? AND status = 'completed'");
        $stmt->execute([$tenantId]);
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }
}
