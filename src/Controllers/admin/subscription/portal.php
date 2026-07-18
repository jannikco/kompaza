<?php

use App\Auth\Auth;
use App\Models\TenantSubscription;
use App\Models\Tenant;
use App\Services\StripeService;

$admin = Auth::admin();
$tenantId = $admin['tenant_id'] ?? currentTenantId();

$subscription = $tenantId ? TenantSubscription::findByTenantId($tenantId) : null;
$tenant = $tenantId ? Tenant::find($tenantId) : null;
$customerId = $subscription['stripe_customer_id'] ?? $tenant['stripe_customer_id'] ?? null;

if (!$customerId) {
    flashMessage('error', 'No active billing account found.');
    redirect('/admin/abonnement');
}

try {
    $stripe = new StripeService(defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : null);
    if (!$stripe->isConfigured()) {
        flashMessage('error', 'Platform billing is not configured. Contact support.');
        redirect('/admin/abonnement');
    }

    $baseUrl = 'https://' . ($tenant['slug'] ?? '') . '.' . PLATFORM_DOMAIN;
    $session = $stripe->createBillingPortalSession(
        $customerId,
        $baseUrl . '/admin/abonnement'
    );

    $url = $session['url'] ?? null;
    if (!$url) {
        throw new \Exception('Billing portal session missing URL.');
    }

    header('Location: ' . $url);
    exit;
} catch (\Exception $e) {
    error_log('Stripe portal error: ' . $e->getMessage());
    flashMessage('error', 'Could not open the billing portal.');
    redirect('/admin/abonnement');
}
