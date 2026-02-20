<?php

use App\Auth\Auth;
use App\Models\Tenant;
use App\Services\StripeService;

$admin = Auth::admin();
$tenantId = $admin['tenant_id'] ?? null;

if (!$tenantId) {
    flashMessage('error', 'Ingen tenant fundet.');
    redirect('/admin/stripe-connect');
}

$tenant = Tenant::find($tenantId);

try {
    // Create Express account if not exists
    $accountId = $tenant['stripe_connect_id'] ?? null;
    if (!$accountId) {
        $account = StripeService::createConnectAccount($admin['email'], [
            'tenant_id' => $tenantId,
            'tenant_slug' => $tenant['slug'],
        ]);
        $accountId = $account->id;
        Tenant::updateStripeConnect($tenantId, [
            'stripe_connect_id' => $accountId,
        ]);
    }

    // Create onboarding link
    $accountLink = StripeService::createAccountLink(
        $accountId,
        APP_URL . '/admin/stripe-connect/forbind',
        APP_URL . '/admin/stripe-connect/callback'
    );

    header('Location: ' . $accountLink->url);
    exit;
} catch (\Exception $e) {
    error_log("Stripe Connect error: " . $e->getMessage());
    flashMessage('error', 'Kunne ikke oprette forbindelse til Stripe.');
    redirect('/admin/stripe-connect');
}
