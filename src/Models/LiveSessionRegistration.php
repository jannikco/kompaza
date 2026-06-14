<?php

namespace App\Models;

use App\Database\Database;

class LiveSessionRegistration {
    public static function find($sessionId, $userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM live_session_registrations WHERE session_id = ? AND user_id = ?");
        $stmt->execute([$sessionId, $userId]);
        return $stmt->fetch();
    }

    public static function allBySession($sessionId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT lsr.*, u.name as user_name, u.email as user_email
            FROM live_session_registrations lsr
            JOIN users u ON lsr.user_id = u.id
            WHERE lsr.session_id = ?
            ORDER BY lsr.registered_at DESC
        ");
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll();
    }

    public static function allByUser($userId, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT lsr.*, ls.title, ls.scheduled_at, ls.duration_minutes, ls.meeting_url, ls.status as session_status
            FROM live_session_registrations lsr
            JOIN live_sessions ls ON lsr.session_id = ls.id
            WHERE lsr.user_id = ? AND lsr.tenant_id = ?
            ORDER BY ls.scheduled_at ASC
        ");
        $stmt->execute([$userId, $tenantId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO live_session_registrations (tenant_id, session_id, user_id)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['session_id'],
            $data['user_id'],
        ]);
        return $db->lastInsertId();
    }

    public static function delete($sessionId, $userId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM live_session_registrations WHERE session_id = ? AND user_id = ?");
        return $stmt->execute([$sessionId, $userId]);
    }

    public static function isRegistered($sessionId, $userId) {
        return self::find($sessionId, $userId) !== false;
    }
}
