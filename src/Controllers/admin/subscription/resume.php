<?php

use App\Auth\Auth;
use App\Models\TenantSubscription;
use App\Services\StripeService;

$admin = Auth::admin();
$tenantId = $admin['tenant_id'] ?? null;

$subscription = $tenantId ? TenantSubscription::findByTenantId($tenantId) : null;

if (!$subscription || !$subscription['stripe_subscription_id']) {
    flashMessage('error', 'Intet abonnement fundet.');
    redirect('/admin/abonnement');
}

try {
    StripeService::resumeSubscription($subscription['stripe_subscription_id']);
    TenantSubscription::update($subscription['id'], [
        'cancel_at_period_end' => 0,
        'canceled_at' => null,
    ]);
    flashMessage('success', 'Dit abonnement er genoptaget.');
} catch (\Exception $e) {
    error_log("Stripe resume error: " . $e->getMessage());
    flashMessage('error', 'Kunne ikke genoptage abonnementet.');
}

redirect('/admin/abonnement');
