<?php

namespace App\Models;

use App\Database\Database;

class Quiz {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM quizzes WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM quizzes WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function getByCourseId($courseId, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM quizzes WHERE course_id = ? AND tenant_id = ? ORDER BY module_id, created_at");
        $stmt->execute([$courseId, $tenantId]);
        return $stmt->fetchAll();
    }

    public static function getByModuleId($moduleId, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM quizzes WHERE module_id = ? AND tenant_id = ?");
        $stmt->execute([$moduleId, $tenantId]);
        return $stmt->fetch();
    }

    public static function getPublishedByCourse($courseId, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM quizzes WHERE course_id = ? AND tenant_id = ? AND status = 'published' ORDER BY module_id, created_at");
        $stmt->execute([$courseId, $tenantId]);
        return $stmt->fetchAll();
    }

    public static function getWithQuestions($quizId, $tenantId) {
        $quiz = self::find($quizId, $tenantId);
        if (!$quiz) return null;

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY position");
        $stmt->execute([$quizId]);
        $questions = $stmt->fetchAll();

        foreach ($questions as &$question) {
            $stmt = $db->prepare("SELECT * FROM quiz_choices WHERE question_id = ? ORDER BY position");
            $stmt->execute([$question['id']]);
            $question['choices'] = $stmt->fetchAll();
        }

        $quiz['questions'] = $questions;
        return $quiz;
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO quizzes (tenant_id, course_id, module_id, title, description, pass_threshold, shuffle_questions, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['course_id'],
            $data['module_id'] ?? null,
            $data['title'],
            $data['description'] ?? null,
            $data['pass_threshold'] ?? 80.00,
            $data['shuffle_questions'] ?? 0,
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
        $stmt = $db->prepare("UPDATE quizzes SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM quizzes WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }
}
