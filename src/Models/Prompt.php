<?php

namespace App\Models;

use App\Database\Database;

class Prompt {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("
                SELECT p.*, pc.name as category_name, pc.slug as category_slug
                FROM prompts p
                JOIN prompt_categories pc ON p.category_id = pc.id
                WHERE p.id = ? AND p.tenant_id = ?
            ");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM prompts WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $status = null) {
        $db = Database::getConnection();
        $sql = "
            SELECT p.*, pc.name as category_name
            FROM prompts p
            JOIN prompt_categories pc ON p.category_id = pc.id
            WHERE p.tenant_id = ?
        ";
        $params = [$tenantId];
        if ($status) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY p.sort_order, p.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function allByCategory($categoryId, $tenantId, $status = 'published') {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM prompts WHERE category_id = ? AND tenant_id = ? AND status = ? ORDER BY sort_order, title");
        $stmt->execute([$categoryId, $tenantId, $status]);
        return $stmt->fetchAll();
    }

    public static function published($tenantId, $categoryId = null) {
        $db = Database::getConnection();
        $sql = "
            SELECT p.*, pc.name as category_name, pc.slug as category_slug
            FROM prompts p
            JOIN prompt_categories pc ON p.category_id = pc.id
            WHERE p.tenant_id = ? AND p.status = 'published'
        ";
        $params = [$tenantId];
        if ($categoryId) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }
        $sql .= " ORDER BY p.is_featured DESC, p.sort_order, p.title";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function search($tenantId, $query) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT p.*, pc.name as category_name, pc.slug as category_slug,
                   MATCH(p.title, p.prompt_text, p.description) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
            FROM prompts p
            JOIN prompt_categories pc ON p.category_id = pc.id
            WHERE p.tenant_id = ? AND p.status = 'published'
            AND MATCH(p.title, p.prompt_text, p.description) AGAINST(? IN NATURAL LANGUAGE MODE)
            ORDER BY relevance DESC
        ");
        $stmt->execute([$query, $tenantId, $query]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO prompts (tenant_id, category_id, title, prompt_text, description, use_case, ai_tool, tags, membership_tier_level, is_featured, status, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['category_id'],
            $data['title'],
            $data['prompt_text'],
            $data['description'] ?? null,
            $data['use_case'] ?? null,
            $data['ai_tool'] ?? null,
            $data['tags'] ?? null,
            $data['membership_tier_level'] ?? 0,
            $data['is_featured'] ?? false,
            $data['status'] ?? 'published',
            $data['sort_order'] ?? 0,
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
        $stmt = $db->prepare("UPDATE prompts SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM prompts WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function incrementCopyCount($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE prompts SET copy_count = copy_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function countByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM prompts WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['count'];
    }
}
