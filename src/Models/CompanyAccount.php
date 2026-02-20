<?php

namespace App\Models;

use App\Database\Database;

class CompanyAccount {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM company_accounts WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM company_accounts WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findByAdmin($userId, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM company_accounts WHERE admin_user_id = ? AND tenant_id = ?");
        $stmt->execute([$userId, $tenantId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT ca.*, u.name as admin_name, u.email as admin_email,
                   (SELECT COUNT(*) FROM team_members WHERE company_account_id = ca.id AND status = 'active') as active_members
            FROM company_accounts ca
            LEFT JOIN users u ON ca.admin_user_id = u.id
            WHERE ca.tenant_id = ?
            ORDER BY ca.created_at DESC
        ");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO company_accounts (tenant_id, company_name, admin_user_id, total_licenses, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['company_name'],
            $data['admin_user_id'],
            $data['total_licenses'] ?? 0,
            $data['status'] ?? 'active',
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
        $stmt = $db->prepare("UPDATE company_accounts SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM company_accounts WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }

    // Team member methods
    public static function getTeamMembers($companyId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT tm.*, u.name as user_name, u.email as user_email
            FROM team_members tm
            LEFT JOIN users u ON tm.user_id = u.id
            WHERE tm.company_account_id = ?
            ORDER BY tm.created_at DESC
        ");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    }

    public static function addTeamMember($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO team_members (company_account_id, user_id, email, name, status, invited_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['company_account_id'],
            $data['user_id'] ?? null,
            $data['email'],
            $data['name'] ?? null,
            $data['status'] ?? 'invited',
        ]);
        return $db->lastInsertId();
    }

    public static function findTeamMember($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM team_members WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function updateTeamMember($id, $data) {
        $db = Database::getConnection();
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $stmt = $db->prepare("UPDATE team_members SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function removeTeamMember($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM team_members WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // License methods
    public static function getLicenses($companyId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT ccl.*, c.title as course_title
            FROM company_course_licenses ccl
            LEFT JOIN courses c ON ccl.course_id = c.id
            WHERE ccl.company_account_id = ?
            ORDER BY ccl.created_at DESC
        ");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    }

    public static function addLicense($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO company_course_licenses (company_account_id, course_id, seats_total, seats_used, expires_at)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['company_account_id'],
            $data['course_id'],
            $data['seats_total'] ?? 1,
            $data['seats_used'] ?? 0,
            $data['expires_at'] ?? null,
        ]);
        return $db->lastInsertId();
    }

    public static function updateLicense($id, $data) {
        $db = Database::getConnection();
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $stmt = $db->prepare("UPDATE company_course_licenses SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function findLicense($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM company_course_licenses WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function removeLicense($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM company_course_licenses WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function countByTenant($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM company_accounts WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['count'];
    }
}
