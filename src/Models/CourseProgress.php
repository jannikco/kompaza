<?php

namespace App\Models;

use App\Database\Database;

class CourseProgress {
    public static function find($enrollmentId, $lessonId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM course_progress WHERE enrollment_id = ? AND lesson_id = ?");
        $stmt->execute([$enrollmentId, $lessonId]);
        return $stmt->fetch();
    }

    public static function allByEnrollment($enrollmentId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM course_progress WHERE enrollment_id = ?");
        $stmt->execute([$enrollmentId]);
        return $stmt->fetchAll();
    }

    public static function completedLessonIds($enrollmentId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT lesson_id FROM course_progress WHERE enrollment_id = ? AND is_completed = 1");
        $stmt->execute([$enrollmentId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public static function markComplete($tenantId, $enrollmentId, $lessonId, $userId) {
        $db = Database::getConnection();
        $existing = self::find($enrollmentId, $lessonId);
        if ($existing) {
            if (!$existing['is_completed']) {
                $stmt = $db->prepare("UPDATE course_progress SET is_completed = 1, completed_at = NOW() WHERE id = ?");
                $stmt->execute([$existing['id']]);
            }
        } else {
            $stmt = $db->prepare("
                INSERT INTO course_progress (tenant_id, enrollment_id, lesson_id, user_id, is_completed, completed_at)
                VALUES (?, ?, ?, ?, 1, NOW())
            ");
            $stmt->execute([$tenantId, $enrollmentId, $lessonId, $userId]);
        }
        CourseEnrollment::recalculateProgress($enrollmentId);
    }

    public static function savePosition($tenantId, $enrollmentId, $lessonId, $userId, $positionSeconds, $watchedPercent) {
        $db = Database::getConnection();
        $existing = self::find($enrollmentId, $lessonId);
        if ($existing) {
            $stmt = $db->prepare("UPDATE course_progress SET video_position_seconds = ?, video_watched_percent = ? WHERE id = ?");
            $stmt->execute([$positionSeconds, $watchedPercent, $existing['id']]);
        } else {
            $stmt = $db->prepare("
                INSERT INTO course_progress (tenant_id, enrollment_id, lesson_id, user_id, video_position_seconds, video_watched_percent)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$tenantId, $enrollmentId, $lessonId, $userId, $positionSeconds, $watchedPercent]);
        }
    }
}
