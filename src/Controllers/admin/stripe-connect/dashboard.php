<?php

use App\Auth\Auth;
use App\Models\Tenant;
use App\Services\StripeService;

$admin = Auth::admin();
$tenantId = $admin['tenant_id'] ?? null;
$tenant = $tenantId ? Tenant::find($tenantId) : null;

if (!$tenant || !$tenant['stripe_connect_id'] || !$tenant['stripe_connect_onboarded']) {
    flashMessage('error', 'Stripe-konto er ikke oprettet endnu.');
    redirect('/admin/stripe-connect');
}

try {
    $loginLink = StripeService::createExpressDashboardLink($tenant['stripe_connect_id']);
    header('Location: ' . $loginLink->url);
    exit;
} catch (\Exception $e) {
    error_log("Stripe Express dashboard error: " . $e->getMessage());
    flashMessage('error', 'Kunne ikke åbne Stripe Dashboard.');
    redirect('/admin/stripe-connect');
}
