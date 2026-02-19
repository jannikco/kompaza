<?php

use App\Models\Ebook;

$tenantId = currentTenantId();
$ebooks = Ebook::allByTenant($tenantId);

view('admin/ebooks/index', [
    'tenant' => currentTenant(),
    'ebooks' => $ebooks,
]);
