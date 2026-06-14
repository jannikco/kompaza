<?php

use App\Models\Webinar;

$tenantId = currentTenantId();
$webinars = Webinar::allByTenant($tenantId);

view('admin/webinars/index', [
    'tenant' => currentTenant(),
    'webinars' => $webinars,
]);
