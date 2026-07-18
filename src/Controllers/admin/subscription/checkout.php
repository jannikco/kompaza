<?php

use App\Auth\Auth;
use App\Models\SubscriptionPlan;
use App\Models\TenantSubscription;
use App\Models\Tenant;
use App\Services\StripeService;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/abonnement');
}

$csrf = $_POST[CSRF_TOKEN_NAME] ?? $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrf)) {
    flashMessage('error', 'Invalid request. Please try again.');
    redirect('/admin/abonnement');
}

$admin = Auth::admin();
$tenantId = $admin['tenant_id'] ?? currentTenantId();
$planId = (int)($_POST['plan_id'] ?? 0);
$interval = $_POST['interval'] ?? 'monthly';

if (!$tenantId || !$planId) {
    flashMessage('error', 'Invalid plan selection.');
    redirect('/admin/abonnement');
}

$plan = SubscriptionPlan::find($planId);
if (!$plan || empty($plan['is_active'])) {
    flashMessage('error', 'Invalid plan.');
    redirect('/admin/abonnement');
}

$priceId = $interval === 'annual' ? ($plan['stripe_price_annual_id'] ?? null) : ($plan['stripe_price_monthly_id'] ?? null);
if (!$priceId) {
    flashMessage('error', 'This plan is not configured in Stripe yet.');
    redirect('/admin/abonnement');
}

try {
    // Platform billing always uses platform Stripe keys
    $stripe = new StripeService(defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : null);

    if (!$stripe->isConfigured()) {
        flashMessage('error', 'Platform billing is not configured. Contact support.');
        redirect('/admin/abonnement');
    }

    $subscription = TenantSubscription::findByTenantId($tenantId);
    $customerId = $subscription['stripe_customer_id'] ?? null;
    $tenant = Tenant::find($tenantId);

    // Prefer stripe_customer_id already on tenant row
    if (!$customerId && !empty($tenant['stripe_customer_id'])) {
        $customerId = $tenant['stripe_customer_id'];
    }

    if (!$customerId) {
        $customer = $stripe->createCustomer(
            $admin['email'],
            $tenant['company_name'] ?? $tenant['name'] ?? '',
            ['tenant_id' => $tenantId]
        );
        $customerId = $customer['id'] ?? null;
        if (!$customerId) {
            throw new \Exception('Stripe did not return a customer id.');
        }
    }

    // Trial only for brand-new subscriptions
    $trialDays = $subscription ? 0 : 7;

    $baseUrl = 'https://' . ($tenant['slug'] ?? '') . '.' . PLATFORM_DOMAIN;
    $session = $stripe->createSubscriptionCheckout(
        $customerId,
        $priceId,
        $baseUrl . '/admin/abonnement/succes?session_id={CHECKOUT_SESSION_ID}',
        $baseUrl . '/admin/abonnement',
        $trialDays > 0 ? $trialDays : null,
        [
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'billing_interval' => $interval,
        ]
    );

    if ($subscription) {
        TenantSubscription::update($subscription['id'], [
            'stripe_customer_id' => $customerId,
            'plan_id' => $planId,
            'billing_interval' => $interval,
        ]);
    } else {
        TenantSubscription::create([
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'stripe_customer_id' => $customerId,
            'billing_interval' => $interval,
            'status' => 'incomplete',
        ]);
    }

    // Keep tenant.stripe_customer_id in sync
    Tenant::update($tenantId, ['stripe_customer_id' => $customerId]);

    $sessionUrl = $session['url'] ?? null;
    if (!$sessionUrl) {
        throw new \Exception('Stripe Checkout session missing URL.');
    }

    header('Location: ' . $sessionUrl);
    exit;
} catch (\Exception $e) {
    error_log('Stripe checkout error: ' . $e->getMessage());
    flashMessage('error', 'Something went wrong starting checkout. Please try again.');
    redirect('/admin/abonnement');
}
