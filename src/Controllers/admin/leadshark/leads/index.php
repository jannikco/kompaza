<?php

use App\Models\Campaign;
use App\Models\LinkedInLead;

$tenantId = currentTenantId();

$search = sanitize($_GET['search'] ?? '');
$campaignId = $_GET['campaign_id'] ?? null;
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Load leads with optional campaign filter
if ($campaignId) {
    $leads = LinkedInLead::allByCampaign($campaignId);
    $totalLeads = LinkedInLead::countByCampaign($campaignId);
} else {
    $leads = LinkedInLead::allByTenant($tenantId, $search ?: null, $perPage, $offset);
    $totalLeads = LinkedInLead::countByTenant($tenantId);
}

$totalPages = max(1, ceil($totalLeads / $perPage));

// Load campaigns for filter dropdown
$campaigns = Campaign::allByTenant($tenantId);

view('admin/leadshark/leads/index', [
    'tenant' => currentTenant(),
    'leads' => $leads,
    'campaigns' => $campaigns,
    'search' => $search,
    'campaignId' => $campaignId,
    'page' => $page,
    'totalPages' => $totalPages,
    'totalLeads' => $totalLeads,
]);
