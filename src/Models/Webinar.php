<?php

namespace App\Models;

use App\Database\Database;

class Webinar {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM webinars WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM webinars WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findBySlug($slug, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM webinars WHERE slug = ? AND tenant_id = ?");
        $stmt->execute([$slug, $tenantId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM webinars WHERE tenant_id = ? ORDER BY created_at DESC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO webinars (tenant_id, title, slug, description, host_name, host_bio, host_image_path,
                webinar_type, scheduled_at, duration_minutes, timezone, embed_url, replay_url,
                registration_headline, registration_subheadline, registration_cta_text, registration_image_path,
                bullet_points, offer_product_id, offer_headline, offer_description,
                reminder_sequence_id, followup_sequence_id, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['title'],
            $data['slug'],
            $data['description'] ?? null,
            $data['host_name'] ?? null,
            $data['host_bio'] ?? null,
            $data['host_image_path'] ?? null,
            $data['webinar_type'] ?? 'live',
            $data['scheduled_at'] ?? null,
            $data['duration_minutes'] ?? 60,
            $data['timezone'] ?? 'Europe/Copenhagen',
            $data['embed_url'] ?? null,
            $data['replay_url'] ?? null,
            $data['registration_headline'] ?? null,
            $data['registration_subheadline'] ?? null,
            $data['registration_cta_text'] ?? 'Register Now',
            $data['registration_image_path'] ?? null,
            $data['bullet_points'] ?? null,
            $data['offer_product_id'] ?? null,
            $data['offer_headline'] ?? null,
            $data['offer_description'] ?? null,
            $data['reminder_sequence_id'] ?? null,
            $data['followup_sequence_id'] ?? null,
            $data['status'] ?? 'draft',
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
        $stmt = $db->prepare("UPDATE webinars SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM webinars WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function incrementRegistrations($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE webinars SET registration_count = registration_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function incrementAttendance($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE webinars SET attendance_count = attendance_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getUpcoming($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM webinars
            WHERE tenant_id = ? AND webinar_type = 'live' AND scheduled_at > NOW() AND status IN ('registration_open','draft')
            ORDER BY scheduled_at ASC
        ");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }
}
