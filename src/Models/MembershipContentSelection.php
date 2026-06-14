<?php

namespace App\Models;

use App\Database\Database;

class MembershipContentSelection {
    public static function allByUser($userId, $tenantId, $contentType = null) {
        $db = Database::getConnection();
        if ($contentType) {
            $stmt = $db->prepare("SELECT * FROM membership_content_selections WHERE user_id = ? AND tenant_id = ? AND content_type = ? ORDER BY selected_at DESC");
            $stmt->execute([$userId, $tenantId, $contentType]);
        } else {
            $stmt = $db->prepare("SELECT * FROM membership_content_selections WHERE user_id = ? AND tenant_id = ? ORDER BY selected_at DESC");
            $stmt->execute([$userId, $tenantId]);
        }
        return $stmt->fetchAll();
    }

    public static function exists($userId, $tenantId, $contentType, $contentId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM membership_content_selections WHERE user_id = ? AND tenant_id = ? AND content_type = ? AND content_id = ?");
        $stmt->execute([$userId, $tenantId, $contentType, $contentId]);
        return $stmt->fetch() !== false;
    }

    public static function countByUser($userId, $tenantId, $contentType) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM membership_content_selections WHERE user_id = ? AND tenant_id = ? AND content_type = ?");
        $stmt->execute([$userId, $tenantId, $contentType]);
        return $stmt->fetch()['count'];
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO membership_content_selections (tenant_id, user_id, content_type, content_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['user_id'],
            $data['content_type'],
            $data['content_id'],
        ]);
        return $db->lastInsertId();
    }

    public static function delete($userId, $tenantId, $contentType, $contentId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM membership_content_selections WHERE user_id = ? AND tenant_id = ? AND content_type = ? AND content_id = ?");
        return $stmt->execute([$userId, $tenantId, $contentType, $contentId]);
    }

    public static function deleteAllByUser($userId, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM membership_content_selections WHERE user_id = ? AND tenant_id = ?");
        return $stmt->execute([$userId, $tenantId]);
    }

    public static function getSelectedIds($userId, $tenantId, $contentType) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT content_id FROM membership_content_selections WHERE user_id = ? AND tenant_id = ? AND content_type = ?");
        $stmt->execute([$userId, $tenantId, $contentType]);
        return array_column($stmt->fetchAll(), 'content_id');
    }
}
