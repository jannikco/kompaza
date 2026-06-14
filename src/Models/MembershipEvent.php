<?php

namespace App\Models;

use App\Database\Database;

class MembershipEvent {
    public static function log($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO membership_events (tenant_id, user_id, membership_id, event_type, stripe_event_id, payload)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['user_id'] ?? null,
            $data['membership_id'] ?? null,
            $data['event_type'],
            $data['stripe_event_id'] ?? null,
            isset($data['payload']) ? json_encode($data['payload']) : null,
        ]);
        return $db->lastInsertId();
    }

    public static function allByTenant($tenantId, $limit = 50) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT me.*, u.name as user_name, u.email as user_email
            FROM membership_events me
            LEFT JOIN users u ON me.user_id = u.id
            WHERE me.tenant_id = ?
            ORDER BY me.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$tenantId, $limit]);
        return $stmt->fetchAll();
    }

    public static function findByStripeEvent($stripeEventId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM membership_events WHERE stripe_event_id = ?");
        $stmt->execute([$stripeEventId]);
        return $stmt->fetch();
    }
}
