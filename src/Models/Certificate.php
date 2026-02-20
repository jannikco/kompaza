<?php

namespace App\Models;

use App\Database\Database;

class Certificate {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM certificates WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM certificates WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findByNumber($certificateNumber) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT c.*, u.name as user_name, u.email as user_email,
                   co.title as course_title, t.company_name as tenant_name, t.slug as tenant_slug
            FROM certificates c
            JOIN users u ON c.user_id = u.id
            JOIN courses co ON c.course_id = co.id
            JOIN tenants t ON c.tenant_id = t.id
            WHERE c.certificate_number = ?
        ");
        $stmt->execute([$certificateNumber]);
        return $stmt->fetch();
    }

    public static function findByUserAndCourse($userId, $courseId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM certificates WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$userId, $courseId]);
        return $stmt->fetch();
    }

    public static function getByUser($userId, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT c.*, co.title as course_title
            FROM certificates c
            JOIN courses co ON c.course_id = co.id
            WHERE c.user_id = ? AND c.tenant_id = ?
            ORDER BY c.issued_at DESC
        ");
        $stmt->execute([$userId, $tenantId]);
        return $stmt->fetchAll();
    }

    public static function getByCourse($courseId, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT c.*, u.name as user_name, u.email as user_email
            FROM certificates c
            JOIN users u ON c.user_id = u.id
            WHERE c.course_id = ? AND c.tenant_id = ?
            ORDER BY c.issued_at DESC
        ");
        $stmt->execute([$courseId, $tenantId]);
        return $stmt->fetchAll();
    }

    public static function allByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT c.*, u.name as user_name, u.email as user_email, co.title as course_title
            FROM certificates c
            JOIN users u ON c.user_id = u.id
            JOIN courses co ON c.course_id = co.id
            WHERE c.tenant_id = ?
            ORDER BY c.issued_at DESC
        ");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function generateNumber() {
        $prefix = 'CERT';
        $year = date('Y');
        $random = strtoupper(bin2hex(random_bytes(4)));
        return "{$prefix}-{$year}-{$random}";
    }

    public static function issue($data) {
        $db = Database::getConnection();
        $certNumber = self::generateNumber();

        $stmt = $db->prepare("
            INSERT INTO certificates (tenant_id, user_id, course_id, certificate_number, score_percentage, pdf_path)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['user_id'],
            $data['course_id'],
            $certNumber,
            $data['score_percentage'] ?? null,
            $data['pdf_path'] ?? null,
        ]);
        return $certNumber;
    }

    public static function revoke($id, $tenantId, $reason = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE certificates SET revoked_at = NOW(), revocation_reason = ? WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$reason, $id, $tenantId]);
    }

    public static function updatePdfPath($id, $pdfPath) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE certificates SET pdf_path = ? WHERE id = ?");
        return $stmt->execute([$pdfPath, $id]);
    }

    public static function countByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM certificates WHERE tenant_id = ? AND revoked_at IS NULL");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['count'];
    }
}
