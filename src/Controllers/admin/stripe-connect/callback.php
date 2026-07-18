<?php

use App\Auth\Auth;
use App\Models\Tenant;
use App\Services\StripeService;

$admin = Auth::admin();
$tenantId = $admin['tenant_id'] ?? currentTenantId();

if (!$tenantId) {
    redirect('/admin/stripe-connect');
}

$tenant = Tenant::find($tenantId);
$accountId = $tenant['stripe_connect_id'] ?? null;

if (!$accountId) {
    flashMessage('error', 'No Stripe account found. Please start Connect again.');
    redirect('/admin/stripe-connect');
}

try {
    $stripe = new StripeService(defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : null);
    if (!$stripe->isConfigured()) {
        flashMessage('error', 'Stripe Connect is not configured. Contact support.');
        redirect('/admin/stripe-connect');
    }

    $account = $stripe->retrieveAccount($accountId);
    $detailsSubmitted = !empty($account['details_submitted']);
    $chargesEnabled = !empty($account['charges_enabled']);
    $payoutsEnabled = !empty($account['payouts_enabled']);

    $connectData = [
        'stripe_connect_onboarded' => $detailsSubmitted ? 1 : 0,
        'stripe_connect_charges_enabled' => $chargesEnabled ? 1 : 0,
        'stripe_connect_payouts_enabled' => $payoutsEnabled ? 1 : 0,
    ];

    if (method_exists(Tenant::class, 'updateStripeConnect')) {
        Tenant::updateStripeConnect($tenantId, $connectData);
    } else {
        Tenant::update($tenantId, $connectData);
    }

    if ($chargesEnabled) {
        flashMessage('success', 'Stripe account connected! You can now receive payments.');
    } else {
        flashMessage('error', 'Stripe onboarding is not finished yet. Click Connect to continue.');
    }
} catch (\Exception $e) {
    error_log('Stripe Connect callback error: ' . $e->getMessage());
    flashMessage('error', 'Could not verify Stripe account.');
}

redirect('/admin/stripe-connect');
