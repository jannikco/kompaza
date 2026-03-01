<?php

namespace App\Models;

use App\Database\Database;

class PostComment {
    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO connectpilot_post_comments (automation_id, tenant_id, comment_urn, commenter_profile_url, commenter_urn, commenter_name, commenter_headline, comment_text, keyword_matched, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['automation_id'],
            $data['tenant_id'],
            $data['comment_urn'],
            $data['commenter_profile_url'] ?? null,
            $data['commenter_urn'] ?? null,
            $data['commenter_name'] ?? null,
            $data['commenter_headline'] ?? null,
            $data['comment_text'] ?? null,
            $data['keyword_matched'] ?? 0,
        ]);
        return $db->lastInsertId();
    }

    public static function findByCommentUrn($urn) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM connectpilot_post_comments WHERE comment_urn = ?");
        $stmt->execute([$urn]);
        return $stmt->fetch();
    }

    public static function allByAutomation($automationId, $limit = 50, $offset = 0) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM connectpilot_post_comments WHERE automation_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$automationId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    public static function countByAutomation($automationId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM connectpilot_post_comments WHERE automation_id = ?");
        $stmt->execute([$automationId]);
        return $stmt->fetch()['count'];
    }

    public static function pendingReplies($automationId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM connectpilot_post_comments WHERE automation_id = ? AND keyword_matched = 1 AND reply_sent = 0 ORDER BY created_at ASC");
        $stmt->execute([$automationId]);
        return $stmt->fetchAll();
    }

    public static function pendingDMs($automationId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM connectpilot_post_comments WHERE automation_id = ? AND keyword_matched = 1 AND dm_sent = 0 ORDER BY created_at ASC");
        $stmt->execute([$automationId]);
        return $stmt->fetchAll();
    }

    public static function markReplied($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE connectpilot_post_comments SET reply_sent = 1, reply_sent_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function markDMSent($id, $leadId = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE connectpilot_post_comments SET dm_sent = 1, dm_sent_at = NOW(), lead_id = ? WHERE id = ?");
        return $stmt->execute([$leadId, $id]);
    }
}
