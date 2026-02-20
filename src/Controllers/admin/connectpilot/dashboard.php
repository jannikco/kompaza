<?php

use App\Models\Campaign;
use App\Models\LinkedInLead;

$tenantId = currentTenantId();
$tenant = currentTenant();

// Load LinkedIn account for this tenant
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("SELECT * FROM linkedin_accounts WHERE tenant_id = ? LIMIT 1");
$stmt->execute([$tenantId]);
$linkedinAccount = $stmt->fetch();

// Campaign stats
$campaigns = Campaign::allByTenant($tenantId);
$activeCampaigns = 0;
$totalConnectionsSent = 0;
$totalMessagesSent = 0;
foreach ($campaigns as $c) {
    if ($c['status'] === 'active') $activeCampaigns++;
    $totalConnectionsSent += (int)($c['leads_contacted'] ?? 0);
    $totalMessagesSent += (int)($c['leads_responded'] ?? 0);
}

$totalLeads = LinkedInLead::countByTenant($tenantId);
$recentCampaigns = array_slice($campaigns, 0, 5);

// Recent activity log
$stmt = $db->prepare("SELECT * FROM connectpilot_activity_log WHERE tenant_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$tenantId]);
$recentActivity = $stmt->fetchAll();

view('admin/connectpilot/dashboard', [
    'tenant' => $tenant,
    'linkedinAccount' => $linkedinAccount,
    'activeCampaigns' => $activeCampaigns,
    'totalLeads' => $totalLeads,
    'totalConnectionsSent' => $totalConnectionsSent,
    'totalMessagesSent' => $totalMessagesSent,
    'recentCampaigns' => $recentCampaigns,
    'recentActivity' => $recentActivity,
]);
