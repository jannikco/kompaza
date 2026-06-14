<?php

use App\Models\TenantSubscription;

// Platform-wide list of all tenant subscriptions (cross-tenant).
$subscriptions = TenantSubscription::all();
$counts = TenantSubscription::countByStatus();
$mrrCents = TenantSubscription::monthlyRecurringRevenue();

view('superadmin/billing/subscriptions', [
    'subscriptions' => $subscriptions,
    'counts'        => $counts,
    'mrrCents'      => $mrrCents,
]);
