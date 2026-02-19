<?php

namespace App\Middleware;

use App\Services\TenantResolver;

class TenantMiddleware {
    /**
     * Ensure a valid tenant is resolved for the current request.
     * Called from router when in tenant routing mode.
     */
    public static function handle(): array {
        $tenant = TenantResolver::current();

        if (!$tenant) {
            http_response_code(404);
            die('Site not found. Please check the URL and try again.');
        }

        // Check tenant status
        if ($tenant['status'] === 'suspended') {
            http_response_code(403);
            die('This account has been suspended. Please contact support at support@kompaza.com.');
        }

        if ($tenant['status'] === 'cancelled') {
            http_response_code(410);
            die('This account has been cancelled.');
        }

        // Check trial expiration
        if ($tenant['subscription_status'] === 'trialing' && $tenant['trial_ends_at']) {
            if (strtotime($tenant['trial_ends_at']) < time()) {
                // Trial expired — allow admin access but block public site
                $request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                if (!str_starts_with($request, '/admin') && $request !== '/login') {
                    http_response_code(402);
                    die('This site\'s trial has expired. The site owner needs to upgrade their plan.');
                }
            }
        }

        return $tenant;
    }

    /**
     * Check if tenant has a specific feature enabled.
     */
    public static function requireFeature(string $feature): void {
        $tenant = TenantResolver::current();
        if (!$tenant) return;

        $key = 'feature_' . $feature;
        if (empty($tenant[$key])) {
            http_response_code(403);
            die('This feature is not available on your current plan.');
        }
    }
}
