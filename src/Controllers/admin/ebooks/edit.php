<?php

use App\Models\Ebook;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$ebook = Ebook::find($id, $tenantId);

if (!$ebook) {
    http_response_code(404);
    view('errors/404');
    exit;
}

view('admin/ebooks/edit', [
    'tenant' => currentTenant(),
    'ebook' => $ebook,
]);
