<?php

use App\Models\User;

$tenantId = currentTenantId();
$search = sanitize($_GET['search'] ?? '');
$customers = User::customersByTenant($tenantId, $search ?: null);

view('admin/customers/index', [
    'tenant' => currentTenant(),
    'customers' => $customers,
    'search' => $search,
]);
