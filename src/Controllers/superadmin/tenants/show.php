<?php

use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Database\Database;

$id = (int)($_GET['id'] ?? 0);
$tenant = Tenant::find($id);
if (!$tenant) {
    flashMessage('error', 'Tenant not found.');
    redirect('/tenants');
}

$db = Database::getConnection();

// Plan name (legacy plans table referenced by tenants.plan_id)
$planName = null;
if (!empty($tenant['plan_id'])) {
    $stmt = $db->prepare("SELECT name FROM plans WHERE id = ?");
    $stmt->execute([$tenant['plan_id']]);
    $planName = $stmt->fetchColumn() ?: null;
}

// Platform subscription record for this tenant (subscription tables may not exist in every env)
$subscription = null;
try {
    $subscription = TenantSubscription::findByTenantId($id);
} catch (\Throwable $e) {
    $subscription = null;
}

// Users counted by role (raw COUNT GROUP BY)
$stmt = $db->prepare("SELECT role, COUNT(*) AS c FROM users WHERE tenant_id = ? GROUP BY role");
$stmt->execute([$id]);
$usersByRole = [];
$totalUsers = 0;
foreach ($stmt->fetchAll() as $row) {
    $usersByRole[$row['role']] = (int)$row['c'];
    $totalUsers += (int)$row['c'];
}

// Lifetime revenue from store orders (exclude cancelled / refunded)
$stmt = $db->prepare("
    SELECT COALESCE(SUM(total_dkk), 0) AS revenue, COUNT(*) AS orders
    FROM orders
    WHERE tenant_id = ? AND status NOT IN ('cancelled', 'refunded')
");
$stmt->execute([$id]);
$revRow = $stmt->fetch();
$revenue = (float)($revRow['revenue'] ?? 0);
$paidOrderCount = (int)($revRow['orders'] ?? 0);

// Content counts per tenant. Each entry: [label, table]. Missing tables are tolerated.
$contentTables = [
    'Products'     => 'products',
    'Orders'       => 'orders',
    'Articles'     => 'articles',
    'Ebooks'       => 'ebooks',
    'Lead Magnets' => 'lead_magnets',
    'Courses'      => 'courses',
    'Newsletters'  => 'newsletters',
];
$contentCounts = [];
foreach ($contentTables as $label => $table) {
    try {
        // Table name is from a fixed whitelist above, never user input.
        $stmt = $db->prepare("SELECT COUNT(*) FROM `{$table}` WHERE tenant_id = ?");
        $stmt->execute([$id]);
        $contentCounts[$label] = (int)$stmt->fetchColumn();
    } catch (\Throwable $e) {
        // Feature/table not present in this environment - skip it.
    }
}
$customerCount = $usersByRole['customer'] ?? 0;

// Feature flags enabled summary
$featureLabels = [
    'feature_blog'          => 'Blog',
    'feature_ebooks'        => 'Ebooks',
    'feature_lead_magnets'  => 'Lead Magnets',
    'feature_orders'        => 'Orders / Shop',
    'feature_connectpilot'  => 'ConnectPilot',
    'feature_courses'       => 'Courses',
    'feature_newsletters'   => 'Newsletters',
    'feature_consultations' => 'Consultations',
    'feature_mastermind'    => 'Mastermind',
    'feature_custom_pages'  => 'Custom Pages',
    'feature_memberships'   => 'Memberships',
    'feature_prompts'       => 'Prompt Library',
    'feature_community'     => 'Community',
];

// Recent audit (last 10 for this tenant)
$stmt = $db->prepare("
    SELECT a.action, a.entity_type, a.entity_id, a.created_at, u.name AS user_name
    FROM audit_logs a
    LEFT JOIN users u ON a.user_id = u.id
    WHERE a.tenant_id = ?
    ORDER BY a.created_at DESC
    LIMIT 10
");
$stmt->execute([$id]);
$recentAudit = $stmt->fetchAll();

view('superadmin/tenants/show', [
    'tenant'        => $tenant,
    'planName'      => $planName,
    'subscription'  => $subscription,
    'usersByRole'   => $usersByRole,
    'totalUsers'    => $totalUsers,
    'revenue'       => $revenue,
    'paidOrderCount' => $paidOrderCount,
    'contentCounts' => $contentCounts,
    'customerCount' => $customerCount,
    'featureLabels' => $featureLabels,
    'recentAudit'   => $recentAudit,
]);
