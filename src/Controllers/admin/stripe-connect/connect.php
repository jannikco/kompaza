<?php

use App\Auth\Auth;
use App\Models\Tenant;
use App\Services\StripeService;

$admin = Auth::admin();
$tenantId = $admin['tenant_id'] ?? currentTenantId();

if (!$tenantId) {
    flashMessage('error', 'No tenant found.');
    redirect('/admin/stripe-connect');
}

$tenant = Tenant::find($tenantId);

try {
    // Connect Express uses platform Stripe keys
    $stripe = new StripeService(defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : null);
    if (!$stripe->isConfigured()) {
        flashMessage('error', 'Stripe Connect is not configured. Contact support.');
        redirect('/admin/stripe-connect');
    }

    $accountId = $tenant['stripe_connect_id'] ?? null;
    if (!$accountId) {
        $account = $stripe->createConnectAccount($admin['email'], [
            'tenant_id' => $tenantId,
            'tenant_slug' => $tenant['slug'] ?? '',
        ]);
        $accountId = $account['id'] ?? null;
        if (!$accountId) {
            throw new \Exception('Stripe did not return a Connect account id.');
        }
        if (method_exists(Tenant::class, 'updateStripeConnect')) {
            Tenant::updateStripeConnect($tenantId, [
                'stripe_connect_id' => $accountId,
            ]);
        } else {
            Tenant::update($tenantId, ['stripe_connect_id' => $accountId]);
        }
    }

    $baseUrl = 'https://' . ($tenant['slug'] ?? '') . '.' . PLATFORM_DOMAIN;
    $accountLink = $stripe->createAccountLink(
        $accountId,
        $baseUrl . '/admin/stripe-connect/forbind',
        $baseUrl . '/admin/stripe-connect/callback'
    );

    $url = $accountLink['url'] ?? null;
    if (!$url) {
        throw new \Exception('Stripe Account Link missing URL.');
    }

    header('Location: ' . $url);
    exit;
} catch (\Exception $e) {
    error_log('Stripe Connect error: ' . $e->getMessage());
    flashMessage('error', 'Could not connect to Stripe. Please try again.');
    redirect('/admin/stripe-connect');
}
