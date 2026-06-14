<?php

namespace App\Models;

use App\Database\Database;

class AbandonedCart {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM abandoned_carts WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM abandoned_carts WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findByEmail($email, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM abandoned_carts WHERE email = ? AND tenant_id = ? AND status = 'active' ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$email, $tenantId]);
        return $stmt->fetch();
    }

    public static function findBySession($sessionId, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM abandoned_carts WHERE session_id = ? AND tenant_id = ? AND status = 'active' ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$sessionId, $tenantId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $status = null, $limit = 50, $offset = 0) {
        $db = Database::getConnection();
        $sql = "SELECT * FROM abandoned_carts WHERE tenant_id = ?";
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

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO abandoned_carts (tenant_id, session_id, customer_id, email, customer_name, cart_data, subtotal_dkk, checkout_started_at, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'active')
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['session_id'] ?? null,
            $data['customer_id'] ?? null,
            $data['email'] ?? null,
            $data['customer_name'] ?? null,
            $data['cart_data'],
            $data['subtotal_dkk'] ?? 0,
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
        $stmt = $db->prepare("UPDATE abandoned_carts SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function markRecovered($id, $orderId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE abandoned_carts SET status = 'recovered', recovered_at = NOW(), recovery_order_id = ? WHERE id = ?");
        return $stmt->execute([$orderId, $id]);
    }

    public static function markAbandoned($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE abandoned_carts SET abandoned_at = NOW() WHERE id = ? AND abandoned_at IS NULL");
        return $stmt->execute([$id]);
    }

    /**
     * Get carts that need recovery emails.
     * Returns carts abandoned more than $delayMinutes ago that haven't received max emails.
     */
    public static function getPendingRecovery($tenantId, $delayMinutes = 60, $maxEmails = 3) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM abandoned_carts
            WHERE tenant_id = ?
              AND status = 'active'
              AND email IS NOT NULL
              AND email != ''
              AND abandoned_at IS NOT NULL
              AND abandoned_at < DATE_SUB(NOW(), INTERVAL ? MINUTE)
              AND emails_sent < ?
              AND (last_email_sent_at IS NULL OR last_email_sent_at < DATE_SUB(NOW(), INTERVAL ? MINUTE))
            ORDER BY abandoned_at ASC
            LIMIT 50
        ");
        $stmt->execute([$tenantId, $delayMinutes, $maxEmails, $delayMinutes]);
        return $stmt->fetchAll();
    }

    public static function markEmailSent($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE abandoned_carts SET emails_sent = emails_sent + 1, last_email_sent_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function expireOld($tenantId, $daysOld = 30) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE abandoned_carts SET status = 'expired' WHERE tenant_id = ? AND status = 'active' AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        return $stmt->execute([$tenantId, $daysOld]);
    }

    public static function countByTenant($tenantId, $status = null) {
        $db = Database::getConnection();
        if ($status) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM abandoned_carts WHERE tenant_id = ? AND status = ?");
            $stmt->execute([$tenantId, $status]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM abandoned_carts WHERE tenant_id = ?");
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetch()['count'];
    }

    public static function totalRecoveredRevenue($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(o.total_dkk), 0) as total
            FROM abandoned_carts ac
            JOIN orders o ON ac.recovery_order_id = o.id
            WHERE ac.tenant_id = ? AND ac.status = 'recovered'
        ");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['total'];
    }
}
