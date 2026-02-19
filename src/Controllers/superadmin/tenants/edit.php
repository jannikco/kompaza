<?php

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    flashMessage('error', 'Tenant not found.');
    redirect('/tenants');
}

$tenant = Tenant::find($id);

if (!$tenant) {
    flashMessage('error', 'Tenant not found.');
    redirect('/tenants');
}

$plans = Plan::all();
$userCount = User::countByTenant($id);

view('superadmin/tenants/edit', [
    'tenant' => $tenant,
    'plans' => $plans,
    'userCount' => $userCount,
]);
