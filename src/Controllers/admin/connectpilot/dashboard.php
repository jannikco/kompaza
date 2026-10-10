<?php

use App\Models\Campaign;
use App\Models\LinkedInLead;
use App\Models\PostAutomation;

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
    // These are the real connectpilot_campaigns columns, maintained by
    // Campaign::updateStats(). The previous names never existed, so the `?? 0`
    // silently pinned both dashboard tiles to zero.
    $totalConnectionsSent += (int)($c['connections_sent'] ?? 0);
    $totalMessagesSent += (int)($c['messages_sent'] ?? 0);
}

$totalLeads = LinkedInLead::countByTenant($tenantId);
$recentCampaigns = array_slice($campaigns, 0, 5);

// Post automation stats
$activePostAutomations = PostAutomation::countActiveByTenant($tenantId);
$totalAutoDMs = PostAutomation::totalDMsSentByTenant($tenantId);
$recentAutomations = PostAutomation::recentByTenant($tenantId, 5);

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
    'activePostAutomations' => $activePostAutomations,
    'totalAutoDMs' => $totalAutoDMs,
    'recentAutomations' => $recentAutomations,
]);
