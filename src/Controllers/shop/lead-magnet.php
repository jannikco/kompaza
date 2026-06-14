<?php

use App\Models\LeadMagnet;

$tenant = currentTenant();
$tenantId = currentTenantId();

$leadMagnet = LeadMagnet::findBySlug($slug, $tenantId);

if (!$leadMagnet || $leadMagnet['status'] !== 'published') {
    http_response_code(404);
    view('errors/404');
    exit;
}

LeadMagnet::incrementViews($leadMagnet['id']);

// A/B test: if a running test targets this lead magnet, render the assigned variant instead
$ab = abAssignVariant($tenantId, 'lead_magnet', (int)$leadMagnet['id']);
if ($ab && $ab['page_id'] !== (int)$leadMagnet['id']) {
    $variantLm = LeadMagnet::find($ab['page_id'], $tenantId);
    if ($variantLm && $variantLm['status'] === 'published') {
        $leadMagnet = $variantLm;
    }
}

$template = $leadMagnet['template'] ?? 'bold';
$validTemplates = ['bold', 'minimal', 'classic', 'split', 'dark'];
if (!in_array($template, $validTemplates)) {
    $template = 'bold';
}

view('shop/lead-magnet-' . $template, [
    'tenant' => $tenant,
    'leadMagnet' => $leadMagnet,
]);
