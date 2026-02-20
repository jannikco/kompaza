<?php

use App\Models\DiscountCode;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$discountCode = DiscountCode::find($id, $tenantId);

if (!$discountCode) {
    http_response_code(404);
    view('errors/404');
    exit;
}

view('admin/discount-codes/form', [
    'tenant' => currentTenant(),
    'discountCode' => $discountCode,
    'isEdit' => true,
]);
