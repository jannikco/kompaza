<?php

use App\Auth\Auth;
use App\Models\TenantSubscription;
use App\Services\StripeService;

$admin = Auth::admin();
$tenantId = $admin['tenant_id'] ?? null;

$subscription = $tenantId ? TenantSubscription::findByTenantId($tenantId) : null;

if (!$subscription || !$subscription['stripe_subscription_id']) {
    flashMessage('error', 'Intet aktivt abonnement fundet.');
    redirect('/admin/abonnement');
}

try {
    StripeService::cancelSubscriptionAtPeriodEnd($subscription['stripe_subscription_id']);
    TenantSubscription::update($subscription['id'], [
        'cancel_at_period_end' => 1,
        'canceled_at' => date('Y-m-d H:i:s'),
    ]);
    flashMessage('success', 'Dit abonnement annulleres ved periodens udløb.');
} catch (\Exception $e) {
    error_log("Stripe cancel error: " . $e->getMessage());
    flashMessage('error', 'Kunne ikke annullere abonnementet.');
}

redirect('/admin/abonnement');
