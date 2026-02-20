<?php

require_once CONTROLLERS_PATH . '/../Helpers/superadmin-layout.php';

use App\Models\TenantSubscription;
use App\Models\SubscriptionPlan;

$mrr = TenantSubscription::monthlyRecurringRevenue();
$statusCounts = TenantSubscription::countByStatus();
$plans = SubscriptionPlan::all();
$subscriptions = TenantSubscription::all();

// Count by plan
$planCounts = [];
foreach ($subscriptions as $sub) {
    if (in_array($sub['status'], ['active', 'trialing'])) {
        $slug = $sub['plan_slug'] ?? 'unknown';
        $planCounts[$slug] = ($planCounts[$slug] ?? 0) + 1;
    }
}

renderSuperadminPage('Omsætning', 'revenue', 'superadmin/revenue/index', compact(
    'mrr', 'statusCounts', 'plans', 'planCounts'
));
