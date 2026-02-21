<?php

use App\Models\Redirect;

$tenantId = currentTenantId();
$redirects = Redirect::allByTenant($tenantId);

view('admin/redirects/index', [
    'tenant' => currentTenant(),
    'redirects' => $redirects,
]);
