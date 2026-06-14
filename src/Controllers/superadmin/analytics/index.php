<?php

/**
 * Superadmin → Platform Analytics (read-only, cross-tenant)
 *
 * Computes platform-wide metrics across ALL tenants using raw prepared SQL
 * plus the TenantSubscription model. No tenant scoping.
 */

use App\Database\Database;
use App\Models\TenantSubscription;

$db = Database::getConnection();

/* ------------------------------------------------------------------ *
 * Recurring revenue (subscription_plans are priced in USD).
 * MRR is returned in cents by the model; convert to dollars for display.
 * ------------------------------------------------------------------ */
$mrrCents = TenantSubscription::monthlyRecurringRevenue();
$mrrUsd   = $mrrCents / 100;
$arrUsd   = $mrrUsd * 12;

/* ------------------------------------------------------------------ *
 * Subscriptions by plan (only active + trialing count toward MRR).
 * ------------------------------------------------------------------ */
$stmt = $db->query("
    SELECT sp.id,
           sp.name,
           sp.slug,
           sp.price_monthly_usd,
           COUNT(ts.id) AS subscriber_count
    FROM subscription_plans sp
    LEFT JOIN tenant_subscriptions ts
        ON ts.plan_id = sp.id
        AND ts.status IN ('active', 'trialing')
    GROUP BY sp.id, sp.name, sp.slug, sp.price_monthly_usd
    ORDER BY sp.price_monthly_usd ASC, sp.name ASC
");
$subsByPlan = $stmt->fetchAll();
$maxPlanSubs = 0;
foreach ($subsByPlan as $row) {
    $maxPlanSubs = max($maxPlanSubs, (int)$row['subscriber_count']);
}

/* ------------------------------------------------------------------ *
 * Subscriptions by status.
 * ------------------------------------------------------------------ */
$subStatusCounts = TenantSubscription::countByStatus();
$totalSubs = array_sum($subStatusCounts);

/* ------------------------------------------------------------------ *
 * New tenants by month (last 12 calendar months, including empty months).
 * ------------------------------------------------------------------ */
$stmt = $db->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS cnt
    FROM tenants
    WHERE created_at >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL 11 MONTH)
    GROUP BY ym
");
$monthlyRaw = [];
foreach ($stmt->fetchAll() as $row) {
    $monthlyRaw[$row['ym']] = (int)$row['cnt'];
}
$newTenantsByMonth = [];
$maxMonthlyTenants = 0;
for ($i = 11; $i >= 0; $i--) {
    $key = date('Y-m', strtotime("first day of -$i month"));
    $count = $monthlyRaw[$key] ?? 0;
    $maxMonthlyTenants = max($maxMonthlyTenants, $count);
    $newTenantsByMonth[] = [
        'label' => date('M Y', strtotime($key . '-01')),
        'count' => $count,
    ];
}

/* ------------------------------------------------------------------ *
 * Tenant status breakdown (trial vs active conversion).
 * ------------------------------------------------------------------ */
$stmt = $db->query("SELECT status, COUNT(*) AS cnt FROM tenants GROUP BY status");
$tenantStatusCounts = [];
foreach ($stmt->fetchAll() as $row) {
    $tenantStatusCounts[$row['status']] = (int)$row['cnt'];
}
$totalTenants    = array_sum($tenantStatusCounts);
$activeTenants   = $tenantStatusCounts['active'] ?? 0;
$trialTenants    = $tenantStatusCounts['trial'] ?? 0;
$conversionBase  = $activeTenants + $trialTenants;
$conversionRate  = $conversionBase > 0 ? round(($activeTenants / $conversionBase) * 100, 1) : 0.0;

/* ------------------------------------------------------------------ *
 * Feature adoption: enabled / active tenants, per feature_* column.
 * ------------------------------------------------------------------ */
$featureColumns = [
    'feature_blog'         => 'Blog',
    'feature_ebooks'       => 'Ebooks',
    'feature_lead_magnets' => 'Lead Magnets',
    'feature_orders'       => 'Orders',
    'feature_connectpilot' => 'ConnectPilot',
    'feature_courses'      => 'Courses',
    'feature_newsletters'  => 'Newsletters',
    'feature_consultations'=> 'Consultations',
    'feature_mastermind'   => 'Mastermind',
    'feature_custom_pages' => 'Custom Pages',
    'feature_memberships'  => 'Memberships',
    'feature_prompts'      => 'Prompt Library',
    'feature_community'    => 'Community',
];
$sumSelects = [];
foreach (array_keys($featureColumns) as $col) {
    // Column names are from a fixed allow-list above, safe to inline.
    $sumSelects[] = "SUM($col) AS $col";
}
$stmt = $db->query(
    "SELECT COUNT(*) AS active_total, " . implode(', ', $sumSelects) .
    " FROM tenants WHERE status = 'active'"
);
$featureRow = $stmt->fetch() ?: [];
$activeTenantCount = (int)($featureRow['active_total'] ?? 0);

$featureAdoption = [];
foreach ($featureColumns as $col => $label) {
    $enabled = (int)($featureRow[$col] ?? 0);
    $pct = $activeTenantCount > 0 ? round(($enabled / $activeTenantCount) * 100, 1) : 0.0;
    $featureAdoption[] = [
        'label'   => $label,
        'enabled' => $enabled,
        'pct'     => $pct,
    ];
}
// Sort by adoption descending for a cleaner read.
usort($featureAdoption, fn($a, $b) => $b['pct'] <=> $a['pct']);

/* ------------------------------------------------------------------ *
 * Top 10 tenants by gross order revenue (revenue-bearing statuses only).
 * orders.total_dkk is the per-order total; currency lives on the tenant.
 * ------------------------------------------------------------------ */
$stmt = $db->query("
    SELECT t.id,
           t.name,
           t.slug,
           t.currency,
           COALESCE(SUM(o.total_dkk), 0) AS revenue,
           COUNT(o.id) AS order_count
    FROM tenants t
    JOIN orders o
        ON o.tenant_id = t.id
        AND o.status IN ('paid', 'processing', 'shipped', 'delivered')
    GROUP BY t.id, t.name, t.slug, t.currency
    ORDER BY revenue DESC
    LIMIT 10
");
$topTenants = $stmt->fetchAll();
$maxTenantRevenue = 0.0;
foreach ($topTenants as $row) {
    $maxTenantRevenue = max($maxTenantRevenue, (float)$row['revenue']);
}

/* ------------------------------------------------------------------ *
 * Total page views (table is created by migration 023 — guard for absence).
 * ------------------------------------------------------------------ */
$totalPageViews = null;
$stmt = $db->query("
    SELECT COUNT(*) AS exists_flag
    FROM information_schema.tables
    WHERE table_schema = DATABASE() AND table_name = 'page_views'
");
if ((int)($stmt->fetch()['exists_flag'] ?? 0) > 0) {
    $totalPageViews = (int)$db->query("SELECT COUNT(*) AS c FROM page_views")->fetch()['c'];
}

view('superadmin/analytics/index', [
    'mrrUsd'             => $mrrUsd,
    'arrUsd'             => $arrUsd,
    'subsByPlan'         => $subsByPlan,
    'maxPlanSubs'        => $maxPlanSubs,
    'subStatusCounts'    => $subStatusCounts,
    'totalSubs'          => $totalSubs,
    'newTenantsByMonth'  => $newTenantsByMonth,
    'maxMonthlyTenants'  => $maxMonthlyTenants,
    'tenantStatusCounts' => $tenantStatusCounts,
    'totalTenants'       => $totalTenants,
    'activeTenants'      => $activeTenants,
    'trialTenants'       => $trialTenants,
    'conversionRate'     => $conversionRate,
    'featureAdoption'    => $featureAdoption,
    'activeTenantCount'  => $activeTenantCount,
    'topTenants'         => $topTenants,
    'maxTenantRevenue'   => $maxTenantRevenue,
    'totalPageViews'     => $totalPageViews,
]);
