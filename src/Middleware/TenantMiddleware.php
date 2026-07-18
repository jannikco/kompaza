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

        // Trial expiration: only lock the public storefront when the tenant is still
        // on trial (status=trial). Manually/paid-activated tenants (status=active)
        // must not be blocked by a stale subscription_status=trialing flag.
        $request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
        $isAdminOrAuth = str_starts_with($request, '/admin')
            || in_array($request, ['/login', '/login/submit', '/registrer', '/forgot-password', '/reset-password'], true)
            || str_starts_with($request, '/api/');

        if (($tenant['status'] ?? '') === 'trial' && !empty($tenant['trial_ends_at'])) {
            if (strtotime($tenant['trial_ends_at']) < time() && !$isAdminOrAuth) {
                http_response_code(402);
                die('This site\'s trial has expired. The site owner needs to upgrade their plan.');
            }
        }

        // Past-due platform subscription: keep admin open so they can update billing;
        // block public storefront until resolved.
        if (($tenant['subscription_status'] ?? '') === 'past_due' && !$isAdminOrAuth) {
            http_response_code(402);
            if (defined('VIEWS_PATH') && file_exists(VIEWS_PATH . '/errors/subscription-lapsed.php')) {
                view('errors/subscription-lapsed', ['tenant' => $tenant]);
                exit;
            }
            die('This site\'s subscription is past due. The site owner needs to update billing.');
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
