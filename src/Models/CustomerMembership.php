<?php

namespace App\Models;

use App\Database\Database;

class CustomerMembership {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM customer_memberships WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM customer_memberships WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findByUser($userId, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT cm.*, mp.name as plan_name, mp.tier_level, mp.max_courses, mp.max_ebooks,
                   mp.can_access_prompts, mp.can_post_community, mp.can_access_live_qa,
                   mp.community_read_only, mp.discount_percent
            FROM customer_memberships cm
            JOIN membership_plans mp ON cm.plan_id = mp.id
            WHERE cm.user_id = ? AND cm.tenant_id = ?
        ");
        $stmt->execute([$userId, $tenantId]);
        return $stmt->fetch();
    }

    public static function findActiveByUser($userId, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT cm.*, mp.name as plan_name, mp.tier_level, mp.max_courses, mp.max_ebooks,
                   mp.can_access_prompts, mp.can_post_community, mp.can_access_live_qa,
                   mp.community_read_only, mp.discount_percent
            FROM customer_memberships cm
            JOIN membership_plans mp ON cm.plan_id = mp.id
            WHERE cm.user_id = ? AND cm.tenant_id = ? AND cm.status IN ('active','trialing')
        ");
        $stmt->execute([$userId, $tenantId]);
        return $stmt->fetch();
    }

    public static function findByStripeSubscription($stripeSubscriptionId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM customer_memberships WHERE stripe_subscription_id = ?");
        $stmt->execute([$stripeSubscriptionId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $status = null) {
        $db = Database::getConnection();
        $sql = "
            SELECT cm.*, mp.name as plan_name, mp.tier_level, u.name as user_name, u.email as user_email
            FROM customer_memberships cm
            JOIN membership_plans mp ON cm.plan_id = mp.id
            JOIN users u ON cm.user_id = u.id
            WHERE cm.tenant_id = ?
        ";
        $params = [$tenantId];
        if ($status) {
            $sql .= " AND cm.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY cm.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function allByPlan($planId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT cm.*, u.name as user_name, u.email as user_email
            FROM customer_memberships cm
            JOIN users u ON cm.user_id = u.id
            WHERE cm.plan_id = ? AND cm.status IN ('active','trialing')
            ORDER BY cm.created_at DESC
        ");
        $stmt->execute([$planId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO customer_memberships (tenant_id, user_id, plan_id, stripe_subscription_id, stripe_customer_id, billing_interval, status, current_period_start, current_period_end)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['user_id'],
            $data['plan_id'],
            $data['stripe_subscription_id'] ?? null,
            $data['stripe_customer_id'] ?? null,
            $data['billing_interval'] ?? 'monthly',
            $data['status'] ?? 'active',
            $data['current_period_start'] ?? null,
            $data['current_period_end'] ?? null,
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
        $stmt = $db->prepare("UPDATE customer_memberships SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function cancel($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE customer_memberships SET status = 'cancelled', cancelled_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function countByTenant($tenantId, $status = null) {
        $db = Database::getConnection();
        if ($status) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM customer_memberships WHERE tenant_id = ? AND status = ?");
            $stmt->execute([$tenantId, $status]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM customer_memberships WHERE tenant_id = ? AND status IN ('active','trialing')");
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetch()['count'];
    }

    public static function getTierLevel($userId, $tenantId) {
        $membership = self::findActiveByUser($userId, $tenantId);
        return $membership ? (int)$membership['tier_level'] : 0;
    }
}
