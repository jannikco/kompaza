<?php

namespace App\Models;

use App\Database\Database;

class Course {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM courses WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM courses WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findBySlug($slug, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM courses WHERE slug = ? AND tenant_id = ? AND status = 'published'");
        $stmt->execute([$slug, $tenantId]);
        return $stmt->fetch();
    }

    public static function findBySlugAnyStatus($slug, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM courses WHERE slug = ? AND tenant_id = ?");
        $stmt->execute([$slug, $tenantId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $status = null) {
        $db = Database::getConnection();
        if ($status) {
            $stmt = $db->prepare("SELECT * FROM courses WHERE tenant_id = ? AND status = ? ORDER BY created_at DESC");
            $stmt->execute([$tenantId, $status]);
        } else {
            $stmt = $db->prepare("SELECT * FROM courses WHERE tenant_id = ? ORDER BY created_at DESC");
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll();
    }

    public static function publishedByTenant($tenantId, $limit = 50) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM courses WHERE tenant_id = ? AND status = 'published' ORDER BY is_featured DESC, created_at DESC LIMIT ?");
        $stmt->execute([$tenantId, $limit]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO courses (tenant_id, slug, title, subtitle, description, short_description, cover_image_path, promo_video_s3_key, pricing_type, price_dkk, compare_price_dkk, subscription_price_monthly_dkk, subscription_price_yearly_dkk, stripe_monthly_price_id, stripe_yearly_price_id, status, is_featured, drip_enabled, drip_interval_days, instructor_name, instructor_bio, instructor_image_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['slug'],
            $data['title'],
            $data['subtitle'] ?? null,
            $data['description'] ?? null,
            $data['short_description'] ?? null,
            $data['cover_image_path'] ?? null,
            $data['promo_video_s3_key'] ?? null,
            $data['pricing_type'] ?? 'free',
            $data['price_dkk'] ?? null,
            $data['compare_price_dkk'] ?? null,
            $data['subscription_price_monthly_dkk'] ?? null,
            $data['subscription_price_yearly_dkk'] ?? null,
            $data['stripe_monthly_price_id'] ?? null,
            $data['stripe_yearly_price_id'] ?? null,
            $data['status'] ?? 'draft',
            $data['is_featured'] ?? 0,
            $data['drip_enabled'] ?? 0,
            $data['drip_interval_days'] ?? null,
            $data['instructor_name'] ?? null,
            $data['instructor_bio'] ?? null,
            $data['instructor_image_path'] ?? null,
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
        $stmt = $db->prepare("UPDATE courses SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM courses WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function incrementViews($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE courses SET view_count = view_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function countByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM courses WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['count'];
    }

    public static function recalculateStats($courseId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT COUNT(*) as total_lessons, COALESCE(SUM(video_duration_seconds), 0) as total_duration
            FROM course_lessons WHERE course_id = ?
        ");
        $stmt->execute([$courseId]);
        $stats = $stmt->fetch();

        $stmt = $db->prepare("UPDATE courses SET total_lessons = ?, total_duration_seconds = ? WHERE id = ?");
        $stmt->execute([$stats['total_lessons'], $stats['total_duration'], $courseId]);
    }

    public static function incrementEnrollment($courseId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE courses SET enrollment_count = enrollment_count + 1 WHERE id = ?");
        return $stmt->execute([$courseId]);
    }

    public static function decrementEnrollment($courseId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE courses SET enrollment_count = GREATEST(0, enrollment_count - 1) WHERE id = ?");
        return $stmt->execute([$courseId]);
    }
}
