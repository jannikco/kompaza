<?php

use App\Auth\Auth;
use App\Models\Tenant;
use App\Services\StripeService;

$admin = Auth::admin();
$tenantId = $admin['tenant_id'] ?? currentTenantId();
$tenant = $tenantId ? Tenant::find($tenantId) : null;

if (!$tenant || empty($tenant['stripe_connect_id']) || empty($tenant['stripe_connect_onboarded'])) {
    flashMessage('error', 'Stripe account is not set up yet.');
    redirect('/admin/stripe-connect');
}

try {
    $stripe = new StripeService(defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : null);
    if (!$stripe->isConfigured()) {
        flashMessage('error', 'Stripe Connect is not configured. Contact support.');
        redirect('/admin/stripe-connect');
    }

    $loginLink = $stripe->createConnectLoginLink($tenant['stripe_connect_id']);
    $url = $loginLink['url'] ?? null;
    if (!$url) {
        throw new \Exception('Express dashboard link missing URL.');
    }

    header('Location: ' . $url);
    exit;
} catch (\Exception $e) {
    error_log('Stripe Express dashboard error: ' . $e->getMessage());
    flashMessage('error', 'Could not open the Stripe Dashboard.');
    redirect('/admin/stripe-connect');
}
