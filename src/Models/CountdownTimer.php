<?php

namespace App\Models;

use App\Database\Database;

class CountdownTimer {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM countdown_timers WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM countdown_timers WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $status = null) {
        $db = Database::getConnection();
        if ($status) {
            $stmt = $db->prepare("SELECT * FROM countdown_timers WHERE tenant_id = ? AND status = ? ORDER BY created_at DESC");
            $stmt->execute([$tenantId, $status]);
        } else {
            $stmt = $db->prepare("SELECT * FROM countdown_timers WHERE tenant_id = ? ORDER BY created_at DESC");
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO countdown_timers (tenant_id, name, timer_type, headline, subheadline, end_date, duration_minutes, redirect_url, expired_action, expired_message, style_preset, bg_color, text_color, accent_color, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['name'],
            $data['timer_type'] ?? 'fixed',
            $data['headline'] ?? null,
            $data['subheadline'] ?? null,
            $data['end_date'] ?? null,
            $data['duration_minutes'] ?? null,
            $data['redirect_url'] ?? null,
            $data['expired_action'] ?? 'hide',
            $data['expired_message'] ?? null,
            $data['style_preset'] ?? 'default',
            $data['bg_color'] ?? '#111827',
            $data['text_color'] ?? '#FFFFFF',
            $data['accent_color'] ?? '#EF4444',
            $data['status'] ?? 'active',
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
        $stmt = $db->prepare("UPDATE countdown_timers SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM countdown_timers WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }
}
