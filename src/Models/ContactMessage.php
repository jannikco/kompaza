<?php

namespace App\Models;

use App\Database\Database;

class ContactMessage {
    public static function find($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM contact_messages WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$id, $tenantId]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $status = null) {
        $db = Database::getConnection();
        if ($status) {
            $stmt = $db->prepare("SELECT * FROM contact_messages WHERE tenant_id = ? AND status = ? ORDER BY created_at DESC");
            $stmt->execute([$tenantId, $status]);
        } else {
            $stmt = $db->prepare("SELECT * FROM contact_messages WHERE tenant_id = ? ORDER BY created_at DESC");
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO contact_messages (tenant_id, name, email, subject, message, ip_address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['name'],
            $data['email'],
            $data['subject'] ?? null,
            $data['message'],
            $data['ip_address'] ?? null,
        ]);
        return $db->lastInsertId();
    }

    public static function updateStatus($id, $tenantId, $status) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE contact_messages SET status = ? WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$status, $id, $tenantId]);
    }

    public static function reply($id, $tenantId, $replyText) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE contact_messages SET admin_reply = ?, status = 'replied', replied_at = NOW() WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$replyText, $id, $tenantId]);
    }

    public static function countUnread($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM contact_messages WHERE tenant_id = ? AND status = 'unread'");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['count'];
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = ? AND tenant_id = ?");
        return $stmt->execute([$id, $tenantId]);
    }
}
