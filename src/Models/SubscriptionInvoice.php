<?php

namespace App\Models;

use App\Database\Database;

class SubscriptionInvoice {

    public static function findByTenantId($tenantId, $limit = 20) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM subscription_invoices WHERE tenant_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$tenantId, $limit]);
        return $stmt->fetchAll();
    }

    public static function findByStripeInvoiceId($stripeInvoiceId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM subscription_invoices WHERE stripe_invoice_id = ?");
        $stmt->execute([$stripeInvoiceId]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO subscription_invoices (tenant_id, stripe_invoice_id, stripe_charge_id,
                amount_cents, currency, status, invoice_url, invoice_pdf, period_start, period_end, paid_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['stripe_invoice_id'] ?? null,
            $data['stripe_charge_id'] ?? null,
            $data['amount_cents'] ?? 0,
            $data['currency'] ?? 'usd',
            $data['status'] ?? 'draft',
            $data['invoice_url'] ?? null,
            $data['invoice_pdf'] ?? null,
            $data['period_start'] ?? null,
            $data['period_end'] ?? null,
            $data['paid_at'] ?? null,
        ]);
        return $db->lastInsertId();
    }

    public static function updateByStripeInvoiceId($stripeInvoiceId, $data) {
        $db = Database::getConnection();
        $fields = [];
        $values = [];

        $allowedFields = [
            'stripe_charge_id', 'amount_cents', 'currency', 'status',
            'invoice_url', 'invoice_pdf', 'period_start', 'period_end', 'paid_at'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) return 0;

        $values[] = $stripeInvoiceId;
        $sql = "UPDATE subscription_invoices SET " . implode(', ', $fields) . " WHERE stripe_invoice_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($values);
        return $stmt->rowCount();
    }
}
