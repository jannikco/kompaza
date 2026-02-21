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

$template = $leadMagnet['template'] ?? 'bold';
$validTemplates = ['bold', 'minimal', 'classic', 'split', 'dark'];
if (!in_array($template, $validTemplates)) {
    $template = 'bold';
}

view('shop/lead-magnet-' . $template, [
    'tenant' => $tenant,
    'leadMagnet' => $leadMagnet,
]);
