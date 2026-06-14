<?php

namespace App\Models;

use App\Database\Database;

class MembershipPlan {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM membership_plans WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM membership_plans WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findBySlug($slug, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM membership_plans WHERE slug = ? AND tenant_id = ?");
        $stmt->execute([$slug, $tenantId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $status = 'active') {
        $db = Database::getConnection();
        if ($status) {
            $stmt = $db->prepare("SELECT * FROM membership_plans WHERE tenant_id = ? AND status = ? ORDER BY sort_order, tier_level");
            $stmt->execute([$tenantId, $status]);
        } else {
            $stmt = $db->prepare("SELECT * FROM membership_plans WHERE tenant_id = ? ORDER BY sort_order, tier_level");
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll();
    }

    public static function getDefault($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM membership_plans WHERE tenant_id = ? AND is_default = 1 AND status = 'active'");
        $stmt->execute([$tenantId]);
        return $stmt->fetch();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO membership_plans (tenant_id, name, slug, tier_level, description, price_monthly, price_yearly, stripe_monthly_price_id, stripe_yearly_price_id, max_courses, max_ebooks, can_access_prompts, can_post_community, can_access_live_qa, community_read_only, discount_percent, is_default, status, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['name'],
            $data['slug'],
            $data['tier_level'] ?? 0,
            $data['description'] ?? null,
            $data['price_monthly'] ?? null,
            $data['price_yearly'] ?? null,
            $data['stripe_monthly_price_id'] ?? null,
            $data['stripe_yearly_price_id'] ?? null,
            $data['max_courses'] ?? null,
            $data['max_ebooks'] ?? null,
            $data['can_access_prompts'] ?? false,
            $data['can_post_community'] ?? false,
            $data['can_access_live_qa'] ?? false,
            $data['community_read_only'] ?? true,
            $data['discount_percent'] ?? 0,
            $data['is_default'] ?? false,
            $data['status'] ?? 'active',
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
        $stmt = $db->prepare("UPDATE membership_plans SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE membership_plans SET status = 'archived' WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function countMembers($planId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM customer_memberships WHERE plan_id = ? AND status IN ('active','trialing')");
        $stmt->execute([$planId]);
        return $stmt->fetch()['count'];
    }

    public static function getMRR($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(
                CASE WHEN cm.billing_interval = 'yearly' THEN mp.price_yearly / 12 ELSE mp.price_monthly END
            ), 0) as mrr
            FROM customer_memberships cm
            JOIN membership_plans mp ON cm.plan_id = mp.id
            WHERE cm.tenant_id = ? AND cm.status IN ('active','trialing')
        ");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['mrr'];
    }

    public static function clearDefault($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE membership_plans SET is_default = 0 WHERE tenant_id = ?");
        return $stmt->execute([$tenantId]);
    }
}
