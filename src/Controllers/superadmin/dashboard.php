<?php

use App\Database\Database;
use App\Models\TenantSubscription;

$db = Database::getConnection();

/* ---------------------------------------------------------------------------
 * Recurring revenue (MRR / ARR)
 * TenantSubscription::monthlyRecurringRevenue() returns cents (USD).
 * The subscription tables may not exist on every environment, so guard it.
 * ------------------------------------------------------------------------- */
$mrrCents = 0;
$subStatusCounts = [];
try {
    $mrrCents = TenantSubscription::monthlyRecurringRevenue();
    $subStatusCounts = TenantSubscription::countByStatus();
} catch (\Throwable $e) {
    $mrrCents = 0;
    $subStatusCounts = [];
}
$mrr = $mrrCents / 100;
$arr = $mrr * 12;

/* ---------------------------------------------------------------------------
 * Tenants by status
 * ------------------------------------------------------------------------- */
$tenantStatusCounts = [
    'trial'     => 0,
    'active'    => 0,
    'suspended' => 0,
    'cancelled' => 0,
];
$stmt = $db->query("SELECT status, COUNT(*) AS c FROM tenants GROUP BY status");
$totalTenants = 0;
foreach ($stmt->fetchAll() as $row) {
    $tenantStatusCounts[$row['status']] = (int) $row['c'];
    $totalTenants += (int) $row['c'];
}

/* ---------------------------------------------------------------------------
 * Total platform users (tenant users only, excludes superadmins)
 * ------------------------------------------------------------------------- */
$stmt = $db->query("SELECT COUNT(*) AS c FROM users WHERE tenant_id IS NOT NULL");
$totalUsers = (int) $stmt->fetch()['c'];

/* ---------------------------------------------------------------------------
 * New tenants in the last 7 / 30 days
 * ------------------------------------------------------------------------- */
$stmt = $db->query("SELECT COUNT(*) AS c FROM tenants WHERE created_at >= (NOW() - INTERVAL 7 DAY)");
$newTenants7 = (int) $stmt->fetch()['c'];

$stmt = $db->query("SELECT COUNT(*) AS c FROM tenants WHERE created_at >= (NOW() - INTERVAL 30 DAY)");
$newTenants30 = (int) $stmt->fetch()['c'];

/* ---------------------------------------------------------------------------
 * Feature adoption — count tenants with each feature_* flag enabled
 * ------------------------------------------------------------------------- */
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

$selectParts = [];
foreach (array_keys($featureColumns) as $col) {
    // Column names are from a fixed whitelist above — safe to interpolate.
    $selectParts[] = "SUM(`$col` = 1) AS `$col`";
}
$stmt = $db->query("SELECT " . implode(', ', $selectParts) . " FROM tenants");
$featureRow = $stmt->fetch() ?: [];

$featureAdoption = [];
foreach ($featureColumns as $col => $label) {
    $featureAdoption[] = [
        'label' => $label,
        'count' => (int) ($featureRow[$col] ?? 0),
    ];
}
// Sort descending by adoption count and keep the top 6 for the bars.
usort($featureAdoption, fn ($a, $b) => $b['count'] <=> $a['count']);
$topFeatures = array_slice($featureAdoption, 0, 6);

/* ---------------------------------------------------------------------------
 * Recent activity — last 10 audit log entries with user + tenant names
 * ------------------------------------------------------------------------- */
$stmt = $db->query("
    SELECT al.id, al.action, al.entity_type, al.entity_id, al.created_at,
           u.name AS user_name, u.email AS user_email,
           t.name AS tenant_name
    FROM audit_logs al
    LEFT JOIN users u ON al.user_id = u.id
    LEFT JOIN tenants t ON al.tenant_id = t.id
    ORDER BY al.created_at DESC, al.id DESC
    LIMIT 10
");
$recentActivity = $stmt->fetchAll();

/* ---------------------------------------------------------------------------
 * Recent tenants — last 8 sign-ups
 * ------------------------------------------------------------------------- */
$stmt = $db->query("
    SELECT t.id, t.name, t.slug, t.status, t.created_at, p.name AS plan_name
    FROM tenants t
    LEFT JOIN plans p ON t.plan_id = p.id
    ORDER BY t.created_at DESC
    LIMIT 8
");
$recentTenants = $stmt->fetchAll();

view('superadmin/dashboard', [
    'mrr'                => $mrr,
    'arr'                => $arr,
    'tenantStatusCounts' => $tenantStatusCounts,
    'totalTenants'       => $totalTenants,
    'totalUsers'         => $totalUsers,
    'newTenants7'        => $newTenants7,
    'newTenants30'       => $newTenants30,
    'subStatusCounts'    => $subStatusCounts,
    'topFeatures'        => $topFeatures,
    'recentActivity'     => $recentActivity,
    'recentTenants'      => $recentTenants,
]);
