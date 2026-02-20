<?php

namespace App\Models;

use App\Database\Database;

class QuizQuestion {
    public static function find($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM quiz_questions WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getByQuizId($quizId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY position");
        $stmt->execute([$quizId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();

        // Get next position
        $stmt = $db->prepare("SELECT COALESCE(MAX(position), 0) + 1 as next_pos FROM quiz_questions WHERE quiz_id = ?");
        $stmt->execute([$data['quiz_id']]);
        $nextPos = $stmt->fetch()['next_pos'];

        $stmt = $db->prepare("
            INSERT INTO quiz_questions (quiz_id, tenant_id, text, position)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['quiz_id'],
            $data['tenant_id'],
            $data['text'],
            $data['position'] ?? $nextPos,
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
        $stmt = $db->prepare("UPDATE quiz_questions SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM quiz_questions WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getChoices($questionId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM quiz_choices WHERE question_id = ? ORDER BY position");
        $stmt->execute([$questionId]);
        return $stmt->fetchAll();
    }

    public static function addChoice($data) {
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT COALESCE(MAX(position), 0) + 1 as next_pos FROM quiz_choices WHERE question_id = ?");
        $stmt->execute([$data['question_id']]);
        $nextPos = $stmt->fetch()['next_pos'];

        $stmt = $db->prepare("INSERT INTO quiz_choices (question_id, text, is_correct, position) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['question_id'],
            $data['text'],
            $data['is_correct'] ?? 0,
            $data['position'] ?? $nextPos,
        ]);
        return $db->lastInsertId();
    }

    public static function updateChoice($id, $data) {
        $db = Database::getConnection();
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $stmt = $db->prepare("UPDATE quiz_choices SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function deleteChoice($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM quiz_choices WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function deleteChoicesByQuestion($questionId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM quiz_choices WHERE question_id = ?");
        return $stmt->execute([$questionId]);
    }
}
