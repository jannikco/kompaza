<?php

use App\Models\User;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$customer = User::find($id);

if (!$customer || $customer['tenant_id'] != $tenantId || $customer['role'] !== 'customer') {
    http_response_code(404);
    view('errors/404');
    exit;
}

view('admin/customers/edit', [
    'tenant' => currentTenant(),
    'customer' => $customer,
]);
