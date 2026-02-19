<?php

use App\Models\Tenant;
use App\Models\User;

$id = (int)($_GET['id'] ?? 0);
$tenant = Tenant::find($id);
if (!$tenant) {
    flashMessage('error', 'Tenant not found.');
    redirect('/tenants');
}

$userCount = User::countByTenant($id);
$adminCount = User::countByTenant($id, 'tenant_admin');
$customerCount = User::countByTenant($id, 'customer');

view('superadmin/tenants/show', [
    'tenant' => $tenant,
    'userCount' => $userCount,
    'adminCount' => $adminCount,
    'customerCount' => $customerCount,
]);
