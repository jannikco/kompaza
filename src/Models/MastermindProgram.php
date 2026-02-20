<?php

namespace App\Models;

use App\Database\Database;

class MastermindProgram {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM mastermind_programs WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM mastermind_programs WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findBySlug($slug, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM mastermind_programs WHERE slug = ? AND tenant_id = ? AND status = 'published'");
        $stmt->execute([$slug, $tenantId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM mastermind_programs WHERE tenant_id = ? ORDER BY created_at DESC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function publishedByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM mastermind_programs WHERE tenant_id = ? AND status = 'published' ORDER BY created_at DESC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO mastermind_programs (tenant_id, title, slug, description, short_description, cover_image_path, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['title'],
            $data['slug'],
            $data['description'] ?? null,
            $data['short_description'] ?? null,
            $data['cover_image_path'] ?? null,
            $data['status'] ?? 'draft',
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
        $stmt = $db->prepare("UPDATE mastermind_programs SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM mastermind_programs WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    // Tiers
    public static function getTiers($programId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM mastermind_tiers WHERE program_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$programId]);
        return $stmt->fetchAll();
    }

    public static function findTier($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM mastermind_tiers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function createTier($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO mastermind_tiers (program_id, name, description, upfront_price_dkk, monthly_price_dkk, max_members, stripe_price_id, features, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['program_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['upfront_price_dkk'] ?? 0,
            $data['monthly_price_dkk'] ?? 0,
            $data['max_members'] ?? null,
            $data['stripe_price_id'] ?? null,
            $data['features'] ?? null,
            $data['sort_order'] ?? 0,
        ]);
        return $db->lastInsertId();
    }

    public static function updateTier($id, $data) {
        $db = Database::getConnection();
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $stmt = $db->prepare("UPDATE mastermind_tiers SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function deleteTier($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM mastermind_tiers WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Enrollments
    public static function getEnrollments($programId, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT me.*, u.name as user_name, u.email as user_email, mt.name as tier_name
            FROM mastermind_enrollments me
            LEFT JOIN users u ON me.user_id = u.id
            LEFT JOIN mastermind_tiers mt ON me.tier_id = mt.id
            WHERE me.program_id = ? AND me.tenant_id = ?
            ORDER BY me.enrolled_at DESC
        ");
        $stmt->execute([$programId, $tenantId]);
        return $stmt->fetchAll();
    }

    public static function findEnrollment($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT me.*, u.name as user_name, u.email as user_email, mt.name as tier_name, mp.title as program_title
            FROM mastermind_enrollments me
            LEFT JOIN users u ON me.user_id = u.id
            LEFT JOIN mastermind_tiers mt ON me.tier_id = mt.id
            LEFT JOIN mastermind_programs mp ON me.program_id = mp.id
            WHERE me.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function createEnrollment($data) {
        $db = Database::getConnection();
        $enrollmentNumber = 'MM-' . strtoupper(substr(md5($data['tenant_id'] . time() . rand()), 0, 8));
        $stmt = $db->prepare("
            INSERT INTO mastermind_enrollments (tenant_id, program_id, tier_id, user_id, enrollment_number, status, stripe_customer_id, stripe_subscription_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['program_id'],
            $data['tier_id'],
            $data['user_id'],
            $enrollmentNumber,
            $data['status'] ?? 'active',
            $data['stripe_customer_id'] ?? null,
            $data['stripe_subscription_id'] ?? null,
        ]);
        return $db->lastInsertId();
    }

    public static function updateEnrollment($id, $data) {
        $db = Database::getConnection();
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $stmt = $db->prepare("UPDATE mastermind_enrollments SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    // Milestones
    public static function getMilestones($enrollmentId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM mastermind_milestones WHERE enrollment_id = ? ORDER BY achieved_at DESC");
        $stmt->execute([$enrollmentId]);
        return $stmt->fetchAll();
    }

    public static function addMilestone($enrollmentId, $type, $notes = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO mastermind_milestones (enrollment_id, milestone_type, notes) VALUES (?, ?, ?)");
        $stmt->execute([$enrollmentId, $type, $notes]);
        return $db->lastInsertId();
    }

    public static function countByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM mastermind_programs WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['count'];
    }

    public static function countEnrollments($programId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM mastermind_enrollments WHERE program_id = ? AND status = 'active'");
        $stmt->execute([$programId]);
        return $stmt->fetch()['count'];
    }
}
