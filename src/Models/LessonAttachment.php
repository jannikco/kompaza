<?php

namespace App\Models;

use App\Database\Database;

class LessonAttachment {
    public static function find($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM lesson_attachments WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getByLessonId($lessonId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM lesson_attachments WHERE lesson_id = ? ORDER BY sort_order");
        $stmt->execute([$lessonId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO lesson_attachments (tenant_id, lesson_id, title, file_path, file_type, file_size, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['lesson_id'],
            $data['title'],
            $data['file_path'],
            $data['file_type'] ?? null,
            $data['file_size'] ?? 0,
            $data['sort_order'] ?? 0,
        ]);
        return $db->lastInsertId();
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM lesson_attachments WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function incrementDownloads($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE lesson_attachments SET download_count = download_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
