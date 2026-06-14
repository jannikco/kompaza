<?php

namespace App\Models;

use App\Database\Database;

class CommunityPost {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("
                SELECT cp.*, u.name as author_name, cc.name as channel_name, cc.slug as channel_slug
                FROM community_posts cp
                JOIN users u ON cp.user_id = u.id
                JOIN community_channels cc ON cp.channel_id = cc.id
                WHERE cp.id = ? AND cp.tenant_id = ?
            ");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM community_posts WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function allByChannel($channelId, $tenantId, $page = 1, $perPage = 20) {
        $db = Database::getConnection();
        $offset = ($page - 1) * $perPage;
        $stmt = $db->prepare("
            SELECT cp.*, u.name as author_name
            FROM community_posts cp
            JOIN users u ON cp.user_id = u.id
            WHERE cp.channel_id = ? AND cp.tenant_id = ? AND cp.is_hidden = 0
            ORDER BY cp.is_pinned DESC, cp.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$channelId, $tenantId, $perPage, $offset]);
        return $stmt->fetchAll();
    }

    public static function countByChannel($channelId, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM community_posts WHERE channel_id = ? AND tenant_id = ? AND is_hidden = 0");
        $stmt->execute([$channelId, $tenantId]);
        return $stmt->fetch()['count'];
    }

    public static function hiddenByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT cp.*, u.name as author_name, cc.name as channel_name
            FROM community_posts cp
            JOIN users u ON cp.user_id = u.id
            JOIN community_channels cc ON cp.channel_id = cc.id
            WHERE cp.tenant_id = ? AND cp.is_hidden = 1
            ORDER BY cp.created_at DESC
        ");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function recentByTenant($tenantId, $limit = 10) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT cp.*, u.name as author_name, cc.name as channel_name, cc.slug as channel_slug
            FROM community_posts cp
            JOIN users u ON cp.user_id = u.id
            JOIN community_channels cc ON cp.channel_id = cc.id
            WHERE cp.tenant_id = ? AND cp.is_hidden = 0
            ORDER BY cp.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$tenantId, $limit]);
        return $stmt->fetchAll();
    }

    public static function countRecentByTenant($tenantId, $days = 7) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT COUNT(*) as count
            FROM community_posts
            WHERE tenant_id = ? AND is_hidden = 0 AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$tenantId, $days]);
        return (int)$stmt->fetch()['count'];
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO community_posts (tenant_id, channel_id, user_id, title, body)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['channel_id'],
            $data['user_id'],
            $data['title'] ?? null,
            $data['body'],
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
        $stmt = $db->prepare("UPDATE community_posts SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM community_posts WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function incrementCommentCount($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE community_posts SET comment_count = comment_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function decrementCommentCount($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE community_posts SET comment_count = GREATEST(comment_count - 1, 0) WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
