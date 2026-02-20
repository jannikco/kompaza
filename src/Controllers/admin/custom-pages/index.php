<?php

use App\Models\CustomPage;

$tenantId = currentTenantId();
$pages = CustomPage::allByTenant($tenantId);

view('admin/custom-pages/index', [
    'tenant' => currentTenant(),
    'pages' => $pages,
]);
