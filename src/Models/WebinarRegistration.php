<?php

namespace App\Models;

use App\Database\Database;

class WebinarRegistration {
    public static function find($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM webinar_registrations WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function findByEmail($webinarId, $email) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM webinar_registrations WHERE webinar_id = ? AND email = ?");
        $stmt->execute([$webinarId, $email]);
        return $stmt->fetch();
    }

    public static function allByWebinar($webinarId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM webinar_registrations WHERE webinar_id = ? ORDER BY registered_at DESC");
        $stmt->execute([$webinarId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO webinar_registrations (webinar_id, user_id, name, email, phone)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['webinar_id'],
            $data['user_id'] ?? null,
            $data['name'],
            $data['email'],
            $data['phone'] ?? null,
        ]);
        return $db->lastInsertId();
    }

    public static function markAttended($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE webinar_registrations SET attended = TRUE, attended_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function delete($id, $tenantId = null) {
        $db = Database::getConnection();
        // webinar_registrations has no tenant_id column; scope via parent webinar when provided.
        $sql = "DELETE FROM webinar_registrations WHERE id = ?";
        $params = [$id];
        if ($tenantId !== null) {
            $sql .= " AND webinar_id IN (SELECT id FROM webinars WHERE tenant_id = ?)";
            $params[] = $tenantId;
        }
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function countByWebinar($webinarId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM webinar_registrations WHERE webinar_id = ?");
        $stmt->execute([$webinarId]);
        return $stmt->fetch()['count'];
    }

    public static function countAttended($webinarId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM webinar_registrations WHERE webinar_id = ? AND attended = TRUE");
        $stmt->execute([$webinarId]);
        return $stmt->fetch()['count'];
    }
}
