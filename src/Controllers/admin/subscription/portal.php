<?php

use App\Auth\Auth;
use App\Models\TenantSubscription;
use App\Services\StripeService;

$admin = Auth::admin();
$tenantId = $admin['tenant_id'] ?? null;

$subscription = $tenantId ? TenantSubscription::findByTenantId($tenantId) : null;

if (!$subscription || !$subscription['stripe_customer_id']) {
    flashMessage('error', 'Intet aktivt abonnement fundet.');
    redirect('/admin/abonnement');
}

try {
    $session = StripeService::createBillingPortalSession(
        $subscription['stripe_customer_id'],
        APP_URL . '/admin/abonnement'
    );
    header('Location: ' . $session->url);
    exit;
} catch (\Exception $e) {
    error_log("Stripe portal error: " . $e->getMessage());
    flashMessage('error', 'Kunne ikke åbne betalingsportalen.');
    redirect('/admin/abonnement');
}
