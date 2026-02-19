<?php

namespace App\Services;

use App\Database\Database;

class TenantResolver {
    private static ?array $currentTenant = null;
    private static bool $resolved = false;
    private static ?string $routingMode = null;

    /**
     * Resolve the current tenant from HTTP_HOST.
     * Returns: 'marketing', 'superadmin', or 'tenant'
     */
    public static function resolve(): string {
        if (self::$resolved) {
            return self::$routingMode;
        }

        $host = strtolower($_SERVER['HTTP_HOST'] ?? 'localhost');
        // Strip port
        $host = preg_replace('/:\d+$/', '', $host);

        $platformDomain = PLATFORM_DOMAIN;

        // 1. Marketing site: kompaza.com or www.kompaza.com
        if ($host === $platformDomain || $host === 'www.' . $platformDomain || $host === 'localhost') {
            self::$routingMode = 'marketing';
            self::$resolved = true;
            return 'marketing';
        }

        // 2. Superadmin: superadmin.kompaza.com
        if ($host === 'superadmin.' . $platformDomain) {
            self::$routingMode = 'superadmin';
            self::$resolved = true;
            return 'superadmin';
        }

        // 3. Subdomain: {slug}.kompaza.com
        if (str_ends_with($host, '.' . $platformDomain)) {
            $slug = str_replace('.' . $platformDomain, '', $host);
            if ($slug && $slug !== 'www' && $slug !== 'superadmin') {
                self::$currentTenant = self::findBySlug($slug);
                if (self::$currentTenant) {
                    self::$routingMode = 'tenant';
                    self::$resolved = true;
                    return 'tenant';
                }
            }
            // Unknown subdomain
            self::$routingMode = 'marketing';
            self::$resolved = true;
            return 'marketing';
        }

        // 4. Custom domain lookup
        self::$currentTenant = self::findByCustomDomain($host);
        if (self::$currentTenant) {
            self::$routingMode = 'tenant';
            self::$resolved = true;
            return 'tenant';
        }

        // Fallback to marketing
        self::$routingMode = 'marketing';
        self::$resolved = true;
        return 'marketing';
    }

    public static function current(): ?array {
        if (!self::$resolved) {
            self::resolve();
        }
        return self::$currentTenant;
    }

    public static function routingMode(): string {
        if (!self::$resolved) {
            self::resolve();
        }
        return self::$routingMode;
    }

    public static function requireTenant(): array {
        $tenant = self::current();
        if (!$tenant) {
            http_response_code(404);
            die('Tenant not found');
        }
        if ($tenant['status'] === 'suspended') {
            http_response_code(403);
            die('This account has been suspended. Please contact support.');
        }
        if ($tenant['status'] === 'cancelled') {
            http_response_code(403);
            die('This account has been cancelled.');
        }
        return $tenant;
    }

    private static function findBySlug(string $slug): ?array {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM tenants WHERE slug = ? AND status IN ('trial','active') LIMIT 1");
            $stmt->execute([$slug]);
            $tenant = $stmt->fetch();
            return $tenant ?: null;
        } catch (\Exception $e) {
            if (APP_DEBUG) error_log("TenantResolver::findBySlug error: " . $e->getMessage());
            return null;
        }
    }

    private static function findByCustomDomain(string $domain): ?array {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT t.* FROM tenants t
                JOIN tenant_domains td ON td.tenant_id = t.id
                WHERE td.domain = ? AND td.ssl_status = 'active' AND t.status IN ('trial','active')
                LIMIT 1
            ");
            $stmt->execute([$domain]);
            $tenant = $stmt->fetch();
            return $tenant ?: null;
        } catch (\Exception $e) {
            if (APP_DEBUG) error_log("TenantResolver::findByCustomDomain error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Override tenant (for superadmin impersonation)
     */
    public static function setTenant(?array $tenant): void {
        self::$currentTenant = $tenant;
        self::$resolved = true;
        self::$routingMode = $tenant ? 'tenant' : 'marketing';
    }
}
