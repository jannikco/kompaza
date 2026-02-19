<?php

use App\Models\User;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$user = User::find($id);

// Verify user belongs to this tenant and is a tenant_admin
if (!$user || $user['tenant_id'] != $tenantId || $user['role'] !== 'tenant_admin') {
    http_response_code(404);
    view('errors/404');
    exit;
}

view('admin/users/edit', [
    'tenant' => currentTenant(),
    'user' => $user,
]);
