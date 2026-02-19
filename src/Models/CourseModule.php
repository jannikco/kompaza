<?php

namespace App\Models;

use App\Database\Database;

class CourseModule {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM course_modules WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM course_modules WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function allByCourse($courseId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM course_modules WHERE course_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public static function allByCourseWithLessons($courseId) {
        $db = Database::getConnection();
        $modules = self::allByCourse($courseId);
        foreach ($modules as &$module) {
            $module['lessons'] = CourseLesson::allByModule($module['id']);
        }
        return $modules;
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO course_modules (course_id, tenant_id, title, description, sort_order, drip_days_after_enrollment)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['course_id'],
            $data['tenant_id'],
            $data['title'],
            $data['description'] ?? null,
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
        $stmt = $db->prepare("UPDATE course_modules SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM course_modules WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function getNextSortOrder($courseId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order FROM course_modules WHERE course_id = ?");
        $stmt->execute([$courseId]);
        return $stmt->fetch()['next_order'];
    }

    public static function reorder($courseId, $orderedIds) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE course_modules SET sort_order = ? WHERE id = ? AND course_id = ?");
        foreach ($orderedIds as $index => $id) {
            $stmt->execute([$index, $id, $courseId]);
        }
    }
}
