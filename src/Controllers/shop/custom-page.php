<?php

use App\Models\CustomPage;

$tenant = currentTenant();
$tenantId = currentTenantId();

$page = CustomPage::findBySlug($slug, $tenantId);

if (!$page) {
    http_response_code(404);
    view('errors/404');
    exit;
}

CustomPage::incrementViews($page['id']);

// Full layout: echo raw HTML directly
if ($page['layout'] === 'full') {
    echo $page['content'];
    exit;
}

// Shop layout: wrap in site header/footer
view('shop/custom-page', [
    'tenant' => $tenant,
    'page' => $page,
]);
