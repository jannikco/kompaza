<?php

namespace App\Models;

use App\Database\Database;

class CommunityChannel {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM community_channels WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM community_channels WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findBySlug($slug, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM community_channels WHERE slug = ? AND tenant_id = ?");
        $stmt->execute([$slug, $tenantId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT cc.*,
                   (SELECT COUNT(*) FROM community_posts cp WHERE cp.channel_id = cc.id AND cp.is_hidden = 0) as post_count
            FROM community_channels cc
            WHERE cc.tenant_id = ?
            ORDER BY cc.sort_order, cc.name
        ");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO community_channels (tenant_id, name, slug, description, icon, read_tier_level, post_tier_level, sort_order, is_locked)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['name'],
            $data['slug'],
            $data['description'] ?? null,
            $data['icon'] ?? null,
            $data['read_tier_level'] ?? 0,
            $data['post_tier_level'] ?? 1,
            $data['sort_order'] ?? 0,
            $data['is_locked'] ?? false,
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
        $stmt = $db->prepare("UPDATE community_channels SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM community_channels WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }
}
