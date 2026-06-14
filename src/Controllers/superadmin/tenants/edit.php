<?php

use App\Models\Tenant;
use App\Models\Plan;

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

view('superadmin/tenants/edit', [
    'tenant' => $tenant,
    'plans'  => $plans,
]);
