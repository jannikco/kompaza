<?php

namespace App\Models;

use App\Database\Database;

class PasswordReset {
    public static function create($email) {
        $db = Database::getConnection();

        // Delete existing tokens for this email
        $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$email]);

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expiresAt]);

        return $token;
    }

    public static function findByToken($token) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public static function delete($token) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM password_resets WHERE token = ?");
        return $stmt->execute([$token]);
    }

    public static function deleteExpired() {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM password_resets WHERE expires_at < NOW()");
        return $stmt->execute();
    }
}
