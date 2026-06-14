<?php

namespace App\Models;

use App\Database\Database;

class CommunityLike {
    public static function exists($userId, $tenantId, $entityType, $entityId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM community_likes WHERE user_id = ? AND tenant_id = ? AND entity_type = ? AND entity_id = ?");
        $stmt->execute([$userId, $tenantId, $entityType, $entityId]);
        return $stmt->fetch() !== false;
    }

    public static function toggle($userId, $tenantId, $entityType, $entityId) {
        $db = Database::getConnection();

        if (self::exists($userId, $tenantId, $entityType, $entityId)) {
            $stmt = $db->prepare("DELETE FROM community_likes WHERE user_id = ? AND tenant_id = ? AND entity_type = ? AND entity_id = ?");
            $stmt->execute([$userId, $tenantId, $entityType, $entityId]);

            $table = $entityType === 'post' ? 'community_posts' : 'community_comments';
            $stmt = $db->prepare("UPDATE $table SET like_count = GREATEST(like_count - 1, 0) WHERE id = ?");
            $stmt->execute([$entityId]);

            return false;
        } else {
            $stmt = $db->prepare("INSERT INTO community_likes (tenant_id, user_id, entity_type, entity_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$tenantId, $userId, $entityType, $entityId]);

            $table = $entityType === 'post' ? 'community_posts' : 'community_comments';
            $stmt = $db->prepare("UPDATE $table SET like_count = like_count + 1 WHERE id = ?");
            $stmt->execute([$entityId]);

            return true;
        }
    }

    public static function count($tenantId, $entityType, $entityId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM community_likes WHERE tenant_id = ? AND entity_type = ? AND entity_id = ?");
        $stmt->execute([$tenantId, $entityType, $entityId]);
        return (int)$stmt->fetch()['count'];
    }

    public static function getLikedIds($userId, $tenantId, $entityType, array $entityIds) {
        if (empty($entityIds)) return [];
        $db = Database::getConnection();
        $placeholders = implode(',', array_fill(0, count($entityIds), '?'));
        $stmt = $db->prepare("SELECT entity_id FROM community_likes WHERE user_id = ? AND tenant_id = ? AND entity_type = ? AND entity_id IN ($placeholders)");
        $stmt->execute(array_merge([$userId, $tenantId, $entityType], $entityIds));
        return array_column($stmt->fetchAll(), 'entity_id');
    }
}
