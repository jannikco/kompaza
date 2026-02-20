<?php

use App\Auth\Auth;
use App\Models\Certificate;

Auth::requireCustomer();

$tenant = currentTenant();
$tenantId = currentTenantId();
$userId = currentUserId();

$certificates = Certificate::getByUser($userId, $tenantId);

view('shop/account/certificates', [
    'tenant' => $tenant,
    'certificates' => $certificates,
]);
