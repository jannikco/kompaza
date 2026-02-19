<?php

use App\Models\Ebook;

$tenant = currentTenant();
$tenantId = currentTenantId();

$ebooks = Ebook::publishedByTenant($tenantId);

view('shop/ebooks', [
    'tenant' => $tenant,
    'ebooks' => $ebooks,
]);
