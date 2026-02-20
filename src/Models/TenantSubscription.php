<?php

namespace App\Models;

use App\Database\Database;

class TenantSubscription {

    public static function findByTenantId($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT ts.*, sp.name as plan_name, sp.slug as plan_slug
            FROM tenant_subscriptions ts
            LEFT JOIN subscription_plans sp ON ts.plan_id = sp.id
            WHERE ts.tenant_id = ?
            ORDER BY ts.created_at DESC LIMIT 1");
        $stmt->execute([$tenantId]);
        return $stmt->fetch();
    }

    public static function findByStripeSubscriptionId($stripeSubscriptionId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM tenant_subscriptions WHERE stripe_subscription_id = ?");
        $stmt->execute([$stripeSubscriptionId]);
        return $stmt->fetch();
    }

    public static function findByStripeCustomerId($stripeCustomerId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM tenant_subscriptions WHERE stripe_customer_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$stripeCustomerId]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO tenant_subscriptions (tenant_id, plan_id, stripe_customer_id, stripe_subscription_id,
                billing_interval, status, trial_ends_at, current_period_start, current_period_end)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['plan_id'],
            $data['stripe_customer_id'] ?? null,
            $data['stripe_subscription_id'] ?? null,
            $data['billing_interval'] ?? 'monthly',
            $data['status'] ?? 'trialing',
            $data['trial_ends_at'] ?? null,
            $data['current_period_start'] ?? null,
            $data['current_period_end'] ?? null,
        ]);
        return $db->lastInsertId();
    }

    public static function update($id, $data) {
        $db = Database::getConnection();
        $fields = [];
        $values = [];

        $allowedFields = [
            'plan_id', 'stripe_customer_id', 'stripe_subscription_id', 'billing_interval',
            'status', 'trial_ends_at', 'current_period_start', 'current_period_end',
            'canceled_at', 'cancel_at_period_end'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) return 0;

        $values[] = $id;
        $sql = "UPDATE tenant_subscriptions SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($values);
        return $stmt->rowCount();
    }

    public static function updateByStripeSubscriptionId($stripeSubscriptionId, $data) {
        $sub = self::findByStripeSubscriptionId($stripeSubscriptionId);
        if (!$sub) return 0;
        return self::update($sub['id'], $data);
    }

    /**
     * Check if a tenant has an active subscription (active or trialing)
     */
    public static function isActive($tenantId): bool {
        $sub = self::findByTenantId($tenantId);
        if (!$sub) return false;
        return in_array($sub['status'], ['active', 'trialing']);
    }

    public static function all() {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT ts.*, sp.name as plan_name, sp.slug as plan_slug, t.name as tenant_name, t.slug as tenant_slug
            FROM tenant_subscriptions ts
            LEFT JOIN subscription_plans sp ON ts.plan_id = sp.id
            LEFT JOIN tenants t ON ts.tenant_id = t.id
            ORDER BY ts.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public static function countByStatus() {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT status, COUNT(*) as count FROM tenant_subscriptions GROUP BY status");
        $results = $stmt->fetchAll();
        $counts = [];
        foreach ($results as $row) {
            $counts[$row['status']] = (int)$row['count'];
        }
        return $counts;
    }

    public static function monthlyRecurringRevenue() {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT SUM(
                CASE ts.billing_interval
                    WHEN 'monthly' THEN sp.price_monthly_usd
                    WHEN 'annual' THEN sp.price_annual_usd
                END
            ) as mrr
            FROM tenant_subscriptions ts
            JOIN subscription_plans sp ON ts.plan_id = sp.id
            WHERE ts.status IN ('active', 'trialing')
        ");
        $result = $stmt->fetch();
        return (int)($result['mrr'] ?? 0);
    }
}
