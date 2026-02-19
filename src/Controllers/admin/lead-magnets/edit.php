<?php

use App\Models\LeadMagnet;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$leadMagnet = LeadMagnet::find($id, $tenantId);

if (!$leadMagnet) {
    http_response_code(404);
    view('errors/404');
    exit;
}

view('admin/lead-magnets/edit', [
    'tenant' => currentTenant(),
    'leadMagnet' => $leadMagnet,
]);
