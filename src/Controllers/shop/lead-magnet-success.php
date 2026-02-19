<?php

use App\Models\LeadMagnet;

$tenant = currentTenant();
$tenantId = currentTenantId();

$leadMagnet = LeadMagnet::findBySlug($slug, $tenantId);

if (!$leadMagnet) {
    http_response_code(404);
    view('errors/404');
    exit;
}

view('shop/lead-magnet-success', [
    'tenant' => $tenant,
    'leadMagnet' => $leadMagnet,
]);
