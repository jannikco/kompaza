<?php

namespace App\Models;

use App\Database\Database;

class EmailSequence {
    public static function find($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM email_sequences WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$id, $tenantId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM email_sequences WHERE tenant_id = ? ORDER BY created_at DESC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function getActiveByTrigger($tenantId, $triggerType, $triggerId = null) {
        $db = Database::getConnection();
        if ($triggerId) {
            $stmt = $db->prepare("SELECT * FROM email_sequences WHERE tenant_id = ? AND trigger_type = ? AND trigger_id = ? AND status = 'active'");
            $stmt->execute([$tenantId, $triggerType, $triggerId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM email_sequences WHERE tenant_id = ? AND trigger_type = ? AND status = 'active'");
            $stmt->execute([$tenantId, $triggerType]);
        }
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO email_sequences (tenant_id, name, trigger_type, trigger_id, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['name'],
            $data['trigger_type'] ?? 'manual',
            $data['trigger_id'] ?? null,
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
        $stmt = $db->prepare("UPDATE email_sequences SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM email_sequences WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function getSteps($sequenceId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM email_sequence_steps WHERE sequence_id = ? ORDER BY sort_order ASC, day_number ASC");
        $stmt->execute([$sequenceId]);
        return $stmt->fetchAll();
    }

    public static function findStep($stepId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM email_sequence_steps WHERE id = ?");
        $stmt->execute([$stepId]);
        return $stmt->fetch();
    }

    public static function createStep($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO email_sequence_steps (sequence_id, day_number, subject, body_html, sort_order)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['sequence_id'],
            $data['day_number'] ?? 1,
            $data['subject'],
            $data['body_html'] ?? null,
            $data['sort_order'] ?? 0,
        ]);
        return $db->lastInsertId();
    }

    public static function updateStep($id, $data) {
        $db = Database::getConnection();
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $stmt = $db->prepare("UPDATE email_sequence_steps SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function deleteStep($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM email_sequence_steps WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function enrollUser($sequenceId, $email, $name = null, $userId = null) {
        $db = Database::getConnection();
        // Check if already enrolled and active
        $stmt = $db->prepare("SELECT id FROM email_sequence_enrollments WHERE sequence_id = ? AND email = ? AND status = 'active'");
        $stmt->execute([$sequenceId, $email]);
        if ($stmt->fetch()) {
            return false; // Already enrolled
        }
        $stmt = $db->prepare("
            INSERT INTO email_sequence_enrollments (sequence_id, user_id, email, name, status, current_step)
            VALUES (?, ?, ?, ?, 'active', 0)
        ");
        $stmt->execute([$sequenceId, $userId, $email, $name]);
        return $db->lastInsertId();
    }

    public static function getDueEmails() {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT e.id as enrollment_id, e.sequence_id, e.email, e.name, e.current_step, e.enrolled_at,
                   s.id as step_id, s.day_number, s.subject, s.body_html,
                   seq.tenant_id, seq.name as sequence_name
            FROM email_sequence_enrollments e
            JOIN email_sequences seq ON e.sequence_id = seq.id AND seq.status = 'active'
            JOIN email_sequence_steps s ON s.sequence_id = e.sequence_id
            WHERE e.status = 'active'
            AND s.sort_order = e.current_step
            AND TIMESTAMPDIFF(DAY, e.enrolled_at, NOW()) >= s.day_number
            AND NOT EXISTS (
                SELECT 1 FROM email_sequence_logs l WHERE l.enrollment_id = e.id AND l.step_id = s.id
            )
            ORDER BY e.enrolled_at ASC
            LIMIT 100
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function logSend($enrollmentId, $stepId, $status = 'sent', $errorMessage = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO email_sequence_logs (enrollment_id, step_id, status, error_message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$enrollmentId, $stepId, $status, $errorMessage]);
    }

    public static function advanceEnrollment($enrollmentId, $sequenceId) {
        $db = Database::getConnection();
        // Get total steps
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM email_sequence_steps WHERE sequence_id = ?");
        $stmt->execute([$sequenceId]);
        $total = $stmt->fetch()['total'];

        $stmt = $db->prepare("UPDATE email_sequence_enrollments SET current_step = current_step + 1 WHERE id = ?");
        $stmt->execute([$enrollmentId]);

        // Check if completed
        $stmt = $db->prepare("SELECT current_step FROM email_sequence_enrollments WHERE id = ?");
        $stmt->execute([$enrollmentId]);
        $enrollment = $stmt->fetch();
        if ($enrollment && $enrollment['current_step'] >= $total) {
            $stmt = $db->prepare("UPDATE email_sequence_enrollments SET status = 'completed', completed_at = NOW() WHERE id = ?");
            $stmt->execute([$enrollmentId]);
        }
    }

    public static function getEnrollments($sequenceId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM email_sequence_enrollments WHERE sequence_id = ? ORDER BY enrolled_at DESC");
        $stmt->execute([$sequenceId]);
        return $stmt->fetchAll();
    }
}
