<?php

namespace App\Models;

use App\Database\Database;

class ConsultationBooking {
    public static function find($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT cb.*, ct.name as type_name, ct.duration_minutes, ct.price_dkk as type_price
            FROM consultation_bookings cb
            LEFT JOIN consultation_types ct ON cb.type_id = ct.id
            WHERE cb.id = ? AND cb.tenant_id = ?
        ");
        $stmt->execute([$id, $tenantId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $status = null) {
        $db = Database::getConnection();
        if ($status) {
            $stmt = $db->prepare("
                SELECT cb.*, ct.name as type_name, ct.duration_minutes
                FROM consultation_bookings cb
                LEFT JOIN consultation_types ct ON cb.type_id = ct.id
                WHERE cb.tenant_id = ? AND cb.status = ?
                ORDER BY cb.created_at DESC
            ");
            $stmt->execute([$tenantId, $status]);
        } else {
            $stmt = $db->prepare("
                SELECT cb.*, ct.name as type_name, ct.duration_minutes
                FROM consultation_bookings cb
                LEFT JOIN consultation_types ct ON cb.type_id = ct.id
                WHERE cb.tenant_id = ?
                ORDER BY cb.created_at DESC
            ");
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $bookingNumber = 'CB-' . strtoupper(substr(md5($data['tenant_id'] . time() . rand()), 0, 8));
        $stmt = $db->prepare("
            INSERT INTO consultation_bookings (tenant_id, type_id, booking_number, customer_name, customer_email, customer_phone, company, project_description, preferred_date, preferred_time, urgency, status, payment_status, stripe_payment_intent_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['type_id'] ?? null,
            $bookingNumber,
            $data['customer_name'],
            $data['customer_email'],
            $data['customer_phone'] ?? null,
            $data['company'] ?? null,
            $data['project_description'] ?? null,
            $data['preferred_date'] ?? null,
            $data['preferred_time'] ?? null,
            $data['urgency'] ?? 'medium',
            $data['status'] ?? 'pending',
            $data['payment_status'] ?? 'unpaid',
            $data['stripe_payment_intent_id'] ?? null,
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
        $stmt = $db->prepare("UPDATE consultation_bookings SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function countByTenant($tenantId, $status = null) {
        $db = Database::getConnection();
        if ($status) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM consultation_bookings WHERE tenant_id = ? AND status = ?");
            $stmt->execute([$tenantId, $status]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM consultation_bookings WHERE tenant_id = ?");
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetch()['count'];
    }

    // Consultation Types
    public static function findType($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM consultation_types WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$id, $tenantId]);
        return $stmt->fetch();
    }

    public static function getActiveTypes($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM consultation_types WHERE tenant_id = ? AND status = 'active' ORDER BY sort_order ASC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function allTypes($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM consultation_types WHERE tenant_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function createType($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO consultation_types (tenant_id, name, description, duration_minutes, price_dkk, status, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['duration_minutes'] ?? 60,
            $data['price_dkk'] ?? 0,
            $data['status'] ?? 'active',
            $data['sort_order'] ?? 0,
        ]);
        return $db->lastInsertId();
    }

    public static function updateType($id, $data) {
        $db = Database::getConnection();
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $stmt = $db->prepare("UPDATE consultation_types SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function deleteType($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM consultation_types WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }
}
