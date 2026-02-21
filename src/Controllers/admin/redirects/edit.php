<?php

use App\Models\Redirect;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$redirect = Redirect::find($id, $tenantId);

if (!$redirect) {
    http_response_code(404);
    view('errors/404');
    exit;
}

view('admin/redirects/edit', [
    'tenant' => currentTenant(),
    'redirect' => $redirect,
]);
