<?php

namespace App\Models;

use App\Database\Database;

class LiveSession {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM live_sessions WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM live_sessions WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT ls.*,
                   (SELECT COUNT(*) FROM live_session_registrations lsr WHERE lsr.session_id = ls.id) as registration_count
            FROM live_sessions ls
            WHERE ls.tenant_id = ?
            ORDER BY ls.scheduled_at DESC
        ");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function upcoming($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT ls.*,
                   (SELECT COUNT(*) FROM live_session_registrations lsr WHERE lsr.session_id = ls.id) as registration_count
            FROM live_sessions ls
            WHERE ls.tenant_id = ? AND ls.scheduled_at > NOW() AND ls.status IN ('scheduled','live')
            ORDER BY ls.scheduled_at ASC
        ");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function past($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT ls.*,
                   (SELECT COUNT(*) FROM live_session_registrations lsr WHERE lsr.session_id = ls.id) as registration_count
            FROM live_sessions ls
            WHERE ls.tenant_id = ? AND (ls.scheduled_at <= NOW() OR ls.status IN ('completed','cancelled'))
            ORDER BY ls.scheduled_at DESC
        ");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO live_sessions (tenant_id, title, description, min_tier_level, scheduled_at, duration_minutes, meeting_url, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['title'],
            $data['description'] ?? null,
            $data['min_tier_level'] ?? 1,
            $data['scheduled_at'],
            $data['duration_minutes'] ?? 60,
            $data['meeting_url'] ?? null,
            $data['status'] ?? 'scheduled',
        ]);
        return $db->lastInsertId();
    }

    public static function update($id, $data, $tenantId = null) {
        $db = Database::getConnection();
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $where = "id = ?";
        if ($tenantId !== null) {
            $where .= " AND tenant_id = ?";
            $values[] = $tenantId;
        }
        $stmt = $db->prepare("UPDATE live_sessions SET " . implode(', ', $fields) . " WHERE " . $where);
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM live_sessions WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }
}
