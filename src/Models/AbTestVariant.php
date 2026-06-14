<?php

namespace App\Models;

use App\Database\Database;

class AbTestVariant {
    public static function find($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM ab_test_variants WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function allByTest($testId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM ab_test_variants WHERE ab_test_id = ? ORDER BY is_control DESC, id ASC");
        $stmt->execute([$testId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO ab_test_variants (ab_test_id, name, variant_type, variant_id, traffic_weight, is_control)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['ab_test_id'],
            $data['name'],
            $data['variant_type'],
            $data['variant_id'],
            $data['traffic_weight'] ?? 50,
            $data['is_control'] ?? false,
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
        $stmt = $db->prepare("UPDATE ab_test_variants SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM ab_test_variants WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function deleteByTest($testId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM ab_test_variants WHERE ab_test_id = ?");
        return $stmt->execute([$testId]);
    }

    public static function incrementViews($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE ab_test_variants SET views = views + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function incrementConversions($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE ab_test_variants SET conversions = conversions + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
