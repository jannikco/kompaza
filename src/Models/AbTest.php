<?php

namespace App\Models;

use App\Database\Database;

class AbTest {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM ab_tests WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM ab_tests WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM ab_tests WHERE tenant_id = ? ORDER BY created_at DESC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function findActiveForPage($tenantId, $pageType, $pageId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT t.*, v.id as variant_id, v.variant_type, v.variant_id as variant_page_id, v.traffic_weight
            FROM ab_tests t
            JOIN ab_test_variants v ON t.id = v.ab_test_id
            WHERE t.tenant_id = ? AND t.original_type = ? AND t.original_id = ? AND t.status = 'running'
            ORDER BY v.is_control DESC
        ");
        $stmt->execute([$tenantId, $pageType, $pageId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO ab_tests (tenant_id, name, test_type, original_type, original_id, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['name'],
            $data['test_type'] ?? 'landing_page',
            $data['original_type'],
            $data['original_id'],
            $data['status'] ?? 'draft',
        ]);
        return $db->lastInsertId();
    }

    public static function update($id, $data, $tenantId = null) {
        $db = Database::getConnection();
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $where = "id = ?";
        if ($tenantId !== null) {
            $where .= " AND tenant_id = ?";
            $values[] = $tenantId;
        }
        $stmt = $db->prepare("UPDATE ab_tests SET " . implode(', ', $fields) . " WHERE " . $where);
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM ab_tests WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function start($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE ab_tests SET status = 'running', started_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function stop($id, $winnerVariantId = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE ab_tests SET status = 'completed', ended_at = NOW(), winner_variant_id = ? WHERE id = ?");
        return $stmt->execute([$winnerVariantId, $id]);
    }

    /**
     * Select a variant for a visitor based on traffic weights.
     * Uses cookie to ensure consistent assignment.
     */
    public static function selectVariant(array $variants): ?array {
        $totalWeight = array_sum(array_column($variants, 'traffic_weight'));
        if ($totalWeight <= 0) return $variants[0] ?? null;

        $rand = mt_rand(1, $totalWeight);
        $cumulative = 0;
        foreach ($variants as $variant) {
            $cumulative += (int)$variant['traffic_weight'];
            if ($rand <= $cumulative) {
                return $variant;
            }
        }
        return $variants[0] ?? null;
    }

    public static function getWithStats($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT t.*, COUNT(v.id) as variant_count
            FROM ab_tests t
            LEFT JOIN ab_test_variants v ON t.id = v.ab_test_id
            WHERE t.tenant_id = ?
            GROUP BY t.id
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }
}
