<?php

namespace App\Models;

use App\Database\Database;

class FunnelStep {
    public static function find($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM funnel_steps WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function allByFunnel($funnelId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM funnel_steps WHERE funnel_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$funnelId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO funnel_steps (funnel_id, name, step_type, sort_order, resource_type, resource_id, custom_url)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['funnel_id'],
            $data['name'],
            $data['step_type'],
            $data['sort_order'] ?? 0,
            $data['resource_type'] ?? null,
            $data['resource_id'] ?? null,
            $data['custom_url'] ?? null,
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
        $stmt = $db->prepare("UPDATE funnel_steps SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM funnel_steps WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function deleteByFunnel($funnelId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM funnel_steps WHERE funnel_id = ?");
        return $stmt->execute([$funnelId]);
    }

    public static function incrementViews($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE funnel_steps SET views = views + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function incrementConversions($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE funnel_steps SET conversions = conversions + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function reorder($funnelId, $orderedIds) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE funnel_steps SET sort_order = ? WHERE id = ? AND funnel_id = ?");
        foreach ($orderedIds as $order => $id) {
            $stmt->execute([$order, $id, $funnelId]);
        }
    }
}
