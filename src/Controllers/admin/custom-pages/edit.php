<?php

use App\Models\CustomPage;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$page = CustomPage::find($id, $tenantId);

if (!$page) {
    http_response_code(404);
    view('errors/404');
    exit;
}

view('admin/custom-pages/form', [
    'tenant' => currentTenant(),
    'page' => $page,
]);
