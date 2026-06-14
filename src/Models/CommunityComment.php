<?php

namespace App\Models;

use App\Database\Database;

class CommunityComment {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("
                SELECT cc.*, u.name as author_name
                FROM community_comments cc
                JOIN users u ON cc.user_id = u.id
                WHERE cc.id = ? AND cc.tenant_id = ?
            ");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM community_comments WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function allByPost($postId, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT cc.*, u.name as author_name
            FROM community_comments cc
            JOIN users u ON cc.user_id = u.id
            WHERE cc.post_id = ? AND cc.tenant_id = ? AND cc.is_hidden = 0
            ORDER BY cc.created_at ASC
        ");
        $stmt->execute([$postId, $tenantId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO community_comments (tenant_id, post_id, user_id, parent_id, body)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['post_id'],
            $data['user_id'],
            $data['parent_id'] ?? null,
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
        $stmt = $db->prepare("UPDATE community_comments SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM community_comments WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }
}
