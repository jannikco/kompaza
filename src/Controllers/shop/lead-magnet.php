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

view('shop/lead-magnet', [
    'tenant' => $tenant,
    'leadMagnet' => $leadMagnet,
]);
