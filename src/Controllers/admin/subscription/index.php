<?php

require_once CONTROLLERS_PATH . '/../Helpers/admin-layout.php';

use App\Models\SubscriptionPlan;
use App\Models\TenantSubscription;
use App\Models\SubscriptionInvoice;
use App\Auth\Auth;

$admin = Auth::admin();
$tenantId = $admin['tenant_id'] ?? null;

$plans = SubscriptionPlan::allActive();
$subscription = $tenantId ? TenantSubscription::findByTenantId($tenantId) : null;
$invoices = $tenantId ? SubscriptionInvoice::findByTenantId($tenantId) : [];

$stripePublishableKey = STRIPE_PUBLISHABLE_KEY;

renderAdminPage('Abonnement', 'subscription', 'admin/subscription/index', compact(
    'plans', 'subscription', 'invoices', 'stripePublishableKey'
));
