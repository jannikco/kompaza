<?php

namespace App\Models;

use App\Database\Database;

class DownloadToken {
    public static function create($data) {
        $db = Database::getConnection();
        $token = bin2hex(random_bytes(32));
        $stmt = $db->prepare("
            INSERT INTO download_tokens (tenant_id, token, tokenable_type, tokenable_id, email, max_downloads, download_count, expires_at, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 0, ?, NOW())
        ");
        $stmt->execute([
            $data['tenant_id'],
            $token,
            $data['tokenable_type'],
            $data['tokenable_id'],
            $data['email'] ?? null,
            $data['max_downloads'] ?? 5,
            $data['expires_at'] ?? date('Y-m-d H:i:s', strtotime('+72 hours')),
        ]);
        return $token;
    }

    public static function findByToken($token) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM download_tokens WHERE token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public static function incrementDownloads($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE download_tokens SET download_count = download_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function isValid($token) {
        $record = self::findByToken($token);
        if (!$record) {
            return false;
        }
        if ($record['expires_at'] && strtotime($record['expires_at']) < time()) {
            return false;
        }
        if ($record['max_downloads'] && $record['download_count'] >= $record['max_downloads']) {
            return false;
        }
        return $record;
    }
}
