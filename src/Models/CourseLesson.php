<?php

namespace App\Models;

use App\Database\Database;

class CourseLesson {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM course_lessons WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM course_lessons WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function allByModule($moduleId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM course_lessons WHERE module_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$moduleId]);
        return $stmt->fetchAll();
    }

    public static function allByCourse($courseId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM course_lessons WHERE course_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public static function firstByCourse($courseId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT cl.* FROM course_lessons cl
            JOIN course_modules cm ON cl.module_id = cm.id
            WHERE cl.course_id = ?
            ORDER BY cm.sort_order ASC, cl.sort_order ASC
            LIMIT 1
        ");
        $stmt->execute([$courseId]);
        return $stmt->fetch();
    }

    public static function nextLesson($courseId, $currentLessonId) {
        $db = Database::getConnection();
        $current = self::find($currentLessonId);
        if (!$current) return null;

        $currentModule = CourseModule::find($current['module_id']);
        if (!$currentModule) return null;

        // Try next in same module
        $stmt = $db->prepare("
            SELECT * FROM course_lessons
            WHERE module_id = ? AND sort_order > ?
            ORDER BY sort_order ASC LIMIT 1
        ");
        $stmt->execute([$current['module_id'], $current['sort_order']]);
        $next = $stmt->fetch();
        if ($next) return $next;

        // Try first lesson of next module
        $stmt = $db->prepare("
            SELECT cm.id FROM course_modules cm
            WHERE cm.course_id = ? AND cm.sort_order > ?
            ORDER BY cm.sort_order ASC LIMIT 1
        ");
        $stmt->execute([$courseId, $currentModule['sort_order']]);
        $nextModule = $stmt->fetch();
        if ($nextModule) {
            $stmt = $db->prepare("SELECT * FROM course_lessons WHERE module_id = ? ORDER BY sort_order ASC LIMIT 1");
            $stmt->execute([$nextModule['id']]);
            return $stmt->fetch();
        }

        return null;
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO course_lessons (module_id, course_id, tenant_id, title, slug, lesson_type, text_content, video_s3_key, video_original_filename, video_duration_seconds, video_file_size_bytes, video_thumbnail_s3_key, video_status, resources, is_preview, sort_order, drip_days_after_enrollment)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['module_id'],
            $data['course_id'],
            $data['tenant_id'],
            $data['title'],
            $data['slug'] ?? null,
            $data['lesson_type'] ?? 'video',
            $data['text_content'] ?? null,
            $data['video_s3_key'] ?? null,
            $data['video_original_filename'] ?? null,
            $data['video_duration_seconds'] ?? null,
            $data['video_file_size_bytes'] ?? null,
            $data['video_thumbnail_s3_key'] ?? null,
            $data['video_status'] ?? null,
            $data['resources'] ?? null,
            $data['is_preview'] ?? 0,
            $data['sort_order'] ?? 0,
            $data['drip_days_after_enrollment'] ?? null,
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
        $stmt = $db->prepare("UPDATE course_lessons SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM course_lessons WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function getNextSortOrder($moduleId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order FROM course_lessons WHERE module_id = ?");
        $stmt->execute([$moduleId]);
        return $stmt->fetch()['next_order'];
    }

    public static function reorder($moduleId, $orderedIds) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE course_lessons SET sort_order = ? WHERE id = ? AND module_id = ?");
        foreach ($orderedIds as $index => $id) {
            $stmt->execute([$index, $id, $moduleId]);
        }
    }

    public static function countByCourse($courseId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM course_lessons WHERE course_id = ?");
        $stmt->execute([$courseId]);
        return $stmt->fetch()['count'];
    }
}
