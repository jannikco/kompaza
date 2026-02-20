<?php

namespace App\Models;

use App\Database\Database;

class QuizAttempt {
    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO quiz_attempts (tenant_id, user_id, quiz_id, score_percentage, passed, answers, ip_address)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['user_id'],
            $data['quiz_id'],
            $data['score_percentage'],
            $data['passed'] ?? 0,
            $data['answers'] ? json_encode($data['answers']) : null,
            $data['ip_address'] ?? null,
        ]);
        return $db->lastInsertId();
    }

    public static function getBestScore($userId, $quizId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT MAX(score_percentage) as best_score FROM quiz_attempts WHERE user_id = ? AND quiz_id = ?");
        $stmt->execute([$userId, $quizId]);
        $result = $stmt->fetch();
        return $result['best_score'] ?? null;
    }

    public static function hasPassed($userId, $quizId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM quiz_attempts WHERE user_id = ? AND quiz_id = ? AND passed = 1 LIMIT 1");
        $stmt->execute([$userId, $quizId]);
        return (bool) $stmt->fetch();
    }

    public static function getByUserAndQuiz($userId, $quizId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM quiz_attempts WHERE user_id = ? AND quiz_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId, $quizId]);
        return $stmt->fetchAll();
    }

    public static function getByUser($userId, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT qa.*, q.title as quiz_title, c.title as course_title
            FROM quiz_attempts qa
            JOIN quizzes q ON qa.quiz_id = q.id
            JOIN courses c ON q.course_id = c.id
            WHERE qa.user_id = ? AND qa.tenant_id = ?
            ORDER BY qa.created_at DESC
        ");
        $stmt->execute([$userId, $tenantId]);
        return $stmt->fetchAll();
    }

    public static function countByQuiz($quizId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM quiz_attempts WHERE quiz_id = ?");
        $stmt->execute([$quizId]);
        return $stmt->fetch()['count'];
    }

    public static function passRateByQuiz($quizId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) as passed_count
            FROM quiz_attempts WHERE quiz_id = ?
        ");
        $stmt->execute([$quizId]);
        $result = $stmt->fetch();
        if ($result['total'] == 0) return 0;
        return round(($result['passed_count'] / $result['total']) * 100, 1);
    }
}
