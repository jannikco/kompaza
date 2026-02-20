<?php

use App\Auth\Auth;
use App\Models\Tenant;
use App\Services\StripeService;

$admin = Auth::admin();
$tenantId = $admin['tenant_id'] ?? null;

if (!$tenantId) {
    redirect('/admin/stripe-connect');
}

$tenant = Tenant::find($tenantId);
$accountId = $tenant['stripe_connect_id'] ?? null;

if (!$accountId) {
    flashMessage('error', 'Ingen Stripe-konto fundet.');
    redirect('/admin/stripe-connect');
}

try {
    $account = StripeService::getConnectAccount($accountId);
    Tenant::updateStripeConnect($tenantId, [
        'stripe_connect_onboarded' => $account->details_submitted ? 1 : 0,
        'stripe_connect_charges_enabled' => $account->charges_enabled ? 1 : 0,
        'stripe_connect_payouts_enabled' => $account->payouts_enabled ? 1 : 0,
    ]);

    if ($account->charges_enabled) {
        flashMessage('success', 'Stripe-konto forbundet! Du kan nu modtage betalinger.');
    } else {
        flashMessage('info', 'Stripe-onboarding er ikke færdig endnu. Klik "Forbind" for at fortsætte.');
    }
} catch (\Exception $e) {
    error_log("Stripe Connect callback error: " . $e->getMessage());
    flashMessage('error', 'Kunne ikke verificere Stripe-konto.');
}

redirect('/admin/stripe-connect');
