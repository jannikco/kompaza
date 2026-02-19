<?php

namespace App\Auth;

use App\Database\Database;
use App\Models\User;

class Auth {
    private static ?array $currentUser = null;

    public static function attempt($email, $password, $tenantId = null) {
        $user = User::findByEmail($email, $tenantId);

        if (!$user) {
            return false;
        }

        if ($user['status'] !== 'active') {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        self::login($user);
        return true;
    }

    public static function login($user) {
        self::$currentUser = $user;

        User::updateLastLogin($user['id']);

        // Create remember token + 180-day cookie
        $token = bin2hex(random_bytes(32));
        $expires = time() + (180 * 24 * 60 * 60);

        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO remember_tokens (user_id, token, expires_at)
            VALUES (?, ?, FROM_UNIXTIME(?))
        ");
        $stmt->execute([$user['id'], hash('sha256', $token), $expires]);

        setcookie('remember_token', $token, $expires, '/', '', true, true);

        logAudit('user_login', 'user', $user['id']);
    }

    public static function logout() {
        $userId = self::$currentUser['id'] ?? null;

        if ($userId) {
            logAudit('user_logout', 'user', $userId);

            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
            $stmt->execute([$userId]);
        }

        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        self::$currentUser = null;
    }

    public static function check(): bool {
        return self::$currentUser !== null;
    }

    public static function user(): ?array {
        return self::$currentUser;
    }

    public static function id(): ?int {
        return self::$currentUser['id'] ?? null;
    }

    public static function role(): ?string {
        return self::$currentUser['role'] ?? null;
    }

    public static function isSuperAdmin(): bool {
        return self::$currentUser !== null && self::$currentUser['role'] === 'superadmin';
    }

    public static function isTenantAdmin(): bool {
        return self::$currentUser !== null && self::$currentUser['role'] === 'tenant_admin';
    }

    public static function isCustomer(): bool {
        return self::$currentUser !== null && self::$currentUser['role'] === 'customer';
    }

    public static function requireAuth() {
        if (!self::check()) {
            setcookie('kz_redirect', $_SERVER['REQUEST_URI'], time() + 300, '/', '', true, true);
            redirect('/login');
        }
    }

    public static function requireSuperAdmin() {
        self::requireAuth();
        if (!self::isSuperAdmin()) {
            http_response_code(403);
            die('Access denied');
        }
    }

    public static function requireTenantAdmin() {
        self::requireAuth();
        if (!self::isTenantAdmin() && !self::isSuperAdmin()) {
            http_response_code(403);
            die('Access denied');
        }
    }

    public static function requireCustomer() {
        self::requireAuth();
        if (!self::isCustomer()) {
            setcookie('kz_redirect', $_SERVER['REQUEST_URI'], time() + 300, '/', '', true, true);
            redirect('/login');
        }
    }

    public static function requireGuest() {
        if (self::check()) {
            if (self::isSuperAdmin()) {
                redirect('/');
            } elseif (self::isTenantAdmin()) {
                redirect('/admin');
            } else {
                redirect('/konto');
            }
        }
    }

    public static function loadFromCookie() {
        if (self::$currentUser !== null) {
            return;
        }

        if (!isset($_COOKIE['remember_token'])) {
            return;
        }

        $token = $_COOKIE['remember_token'];
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT user_id
            FROM remember_tokens
            WHERE token = ?
            AND expires_at > NOW()
        ");
        $stmt->execute([hash('sha256', $token)]);
        $tokenData = $stmt->fetch();

        if (!$tokenData) {
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
            return;
        }

        $user = User::find($tokenData['user_id']);
        if ($user && $user['status'] === 'active') {
            self::$currentUser = $user;

            // Rotate token
            $newToken = bin2hex(random_bytes(32));
            $expires = time() + (180 * 24 * 60 * 60);

            $stmt = $db->prepare("
                UPDATE remember_tokens
                SET token = ?, expires_at = FROM_UNIXTIME(?)
                WHERE user_id = ? AND token = ?
            ");
            $stmt->execute([
                hash('sha256', $newToken),
                $expires,
                $user['id'],
                hash('sha256', $token)
            ]);

            setcookie('remember_token', $newToken, $expires, '/', '', true, true);
        }
    }
}
