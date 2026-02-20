<?php

use App\Auth\Auth;
use App\Models\SubscriptionPlan;
use App\Models\TenantSubscription;
use App\Models\Tenant;
use App\Services\StripeService;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/abonnement');
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    flashMessage('error', 'Ugyldig anmodning.');
    redirect('/admin/abonnement');
}

$admin = Auth::admin();
$tenantId = $admin['tenant_id'] ?? null;
$planId = (int)($_POST['plan_id'] ?? 0);
$interval = $_POST['interval'] ?? 'monthly';

if (!$tenantId || !$planId) {
    flashMessage('error', 'Ugyldige data.');
    redirect('/admin/abonnement');
}

$plan = SubscriptionPlan::find($planId);
if (!$plan || !$plan['is_active']) {
    flashMessage('error', 'Ugyldig plan.');
    redirect('/admin/abonnement');
}

$priceId = $interval === 'annual' ? $plan['stripe_price_annual_id'] : $plan['stripe_price_monthly_id'];
if (!$priceId) {
    flashMessage('error', 'Denne plan er ikke konfigureret i Stripe endnu.');
    redirect('/admin/abonnement');
}

try {
    // Get or create Stripe customer
    $subscription = TenantSubscription::findByTenantId($tenantId);
    $customerId = $subscription['stripe_customer_id'] ?? null;

    if (!$customerId) {
        $tenant = Tenant::find($tenantId);
        $customer = StripeService::createCustomer(
            $admin['email'],
            $tenant['name'],
            ['tenant_id' => $tenantId]
        );
        $customerId = $customer->id;
    }

    // Determine trial days (only for new subscriptions)
    $trialDays = $subscription ? 0 : 7;

    $session = StripeService::createSubscriptionCheckout(
        $customerId,
        $priceId,
        APP_URL . '/admin/abonnement/succes?session_id={CHECKOUT_SESSION_ID}',
        APP_URL . '/admin/abonnement',
        $trialDays
    );

    // Create or update local subscription record
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

    header('Location: ' . $session->url);
    exit;
} catch (\Exception $e) {
    error_log("Stripe checkout error: " . $e->getMessage());
    flashMessage('error', 'Der opstod en fejl. Prøv igen.');
    redirect('/admin/abonnement');
}
