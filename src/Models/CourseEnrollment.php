<?php

namespace App\Models;

use App\Database\Database;

class CourseEnrollment {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM course_enrollments WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM course_enrollments WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findByUserAndCourse($userId, $courseId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM course_enrollments WHERE user_id = ? AND course_id = ? AND status = 'active'");
        $stmt->execute([$userId, $courseId]);
        return $stmt->fetch();
    }

    public static function findAnyByUserAndCourse($userId, $courseId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM course_enrollments WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$userId, $courseId]);
        return $stmt->fetch();
    }

    public static function allByCourse($courseId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT ce.*, u.name as user_name, u.email as user_email
            FROM course_enrollments ce
            JOIN users u ON ce.user_id = u.id
            WHERE ce.course_id = ?
            ORDER BY ce.enrolled_at DESC
        ");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public static function allByUser($userId, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT ce.*, c.title as course_title, c.slug as course_slug, c.cover_image_path, c.total_lessons as course_total_lessons
            FROM course_enrollments ce
            JOIN courses c ON ce.course_id = c.id
            WHERE ce.user_id = ? AND ce.tenant_id = ? AND ce.status = 'active'
            ORDER BY ce.last_accessed_at DESC, ce.enrolled_at DESC
        ");
        $stmt->execute([$userId, $tenantId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO course_enrollments (tenant_id, course_id, user_id, enrollment_source, order_id, stripe_subscription_id, status, total_lessons, enrolled_at, expires_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['course_id'],
            $data['user_id'],
            $data['enrollment_source'] ?? 'free',
            $data['order_id'] ?? null,
            $data['stripe_subscription_id'] ?? null,
            $data['status'] ?? 'active',
            $data['total_lessons'] ?? 0,
            $data['expires_at'] ?? null,
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
        $stmt = $db->prepare("UPDATE course_enrollments SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM course_enrollments WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function updateLastAccessed($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE course_enrollments SET last_accessed_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function recalculateProgress($enrollmentId) {
        $db = Database::getConnection();
        $enrollment = self::find($enrollmentId);
        if (!$enrollment) return;

        $stmt = $db->prepare("SELECT COUNT(*) as completed FROM course_progress WHERE enrollment_id = ? AND is_completed = 1");
        $stmt->execute([$enrollmentId]);
        $completedCount = $stmt->fetch()['completed'];

        $stmt = $db->prepare("SELECT COUNT(*) as total FROM course_lessons WHERE course_id = ?");
        $stmt->execute([$enrollment['course_id']]);
        $totalCount = $stmt->fetch()['total'];

        $percent = $totalCount > 0 ? round(($completedCount / $totalCount) * 100, 2) : 0;
        $completedAt = ($totalCount > 0 && $completedCount >= $totalCount) ? date('Y-m-d H:i:s') : null;

        $stmt = $db->prepare("UPDATE course_enrollments SET completed_lessons = ?, total_lessons = ?, progress_percent = ?, completed_at = ? WHERE id = ?");
        $stmt->execute([$completedCount, $totalCount, $percent, $completedAt, $enrollmentId]);
    }

    public static function countByCourse($courseId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM course_enrollments WHERE course_id = ? AND status = 'active'");
        $stmt->execute([$courseId]);
        return $stmt->fetch()['count'];
    }

    public static function findByStripeSubscription($stripeSubscriptionId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM course_enrollments WHERE stripe_subscription_id = ? AND status = 'active'");
        $stmt->execute([$stripeSubscriptionId]);
        return $stmt->fetchAll();
    }
}
