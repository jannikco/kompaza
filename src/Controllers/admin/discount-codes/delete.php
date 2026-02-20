<?php

use App\Models\DiscountCode;

if (!isPost()) redirect('/admin/discount-codes');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/discount-codes');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/discount-codes');

$discountCode = DiscountCode::find($id, $tenantId);
if (!$discountCode) {
    flashMessage('error', 'Discount code not found.');
    redirect('/admin/discount-codes');
}

DiscountCode::delete($id, $tenantId);

logAudit('discount_code_deleted', 'discount_code', $id);
flashMessage('success', 'Discount code deleted successfully.');
redirect('/admin/discount-codes');
