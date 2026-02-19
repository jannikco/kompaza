<?php

use App\Models\Ebook;

$tenant = currentTenant();
$tenantId = currentTenantId();

$ebook = Ebook::findBySlug($slug, $tenantId);

if (!$ebook) {
    http_response_code(404);
    view('errors/404');
    exit;
}

Ebook::incrementViews($ebook['id']);

view('shop/ebook', [
    'tenant' => $tenant,
    'ebook' => $ebook,
]);
