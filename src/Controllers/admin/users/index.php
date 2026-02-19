<?php

use App\Models\User;

$tenantId = currentTenantId();
$users = User::allByTenant($tenantId, 'tenant_admin');

view('admin/users/index', [
    'tenant' => currentTenant(),
    'users' => $users,
]);
