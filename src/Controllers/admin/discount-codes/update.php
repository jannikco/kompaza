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

$code = strtoupper(trim(sanitize($_POST['code'] ?? '')));
$type = sanitize($_POST['type'] ?? 'percentage');
$value = (float)($_POST['value'] ?? 0);
$minOrderDkk = !empty($_POST['min_order_dkk']) ? (float)$_POST['min_order_dkk'] : null;
$maxUses = !empty($_POST['max_uses']) ? (int)$_POST['max_uses'] : null;
$appliesTo = sanitize($_POST['applies_to'] ?? 'all');
$expiresAt = !empty($_POST['expires_at']) ? sanitize($_POST['expires_at']) : null;
$status = sanitize($_POST['status'] ?? 'active');

// Validation
if (empty($code)) {
    flashMessage('error', 'Discount code is required.');
    redirect('/admin/discount-codes/edit?id=' . $id);
}

if ($value <= 0) {
    flashMessage('error', 'Discount value must be greater than zero.');
    redirect('/admin/discount-codes/edit?id=' . $id);
}

if ($type === 'percentage' && $value > 100) {
    flashMessage('error', 'Percentage discount cannot exceed 100%.');
    redirect('/admin/discount-codes/edit?id=' . $id);
}

if (!in_array($type, ['percentage', 'fixed'])) {
    flashMessage('error', 'Invalid discount type.');
    redirect('/admin/discount-codes/edit?id=' . $id);
}

if (!in_array($appliesTo, ['all', 'courses', 'products', 'ebooks'])) {
    flashMessage('error', 'Invalid "applies to" value.');
    redirect('/admin/discount-codes/edit?id=' . $id);
}

if (!in_array($status, ['active', 'inactive'])) {
    flashMessage('error', 'Invalid status.');
    redirect('/admin/discount-codes/edit?id=' . $id);
}

// Check for duplicate code (excluding current)
$existing = DiscountCode::findByCode($code, $tenantId);
if ($existing && $existing['id'] != $id) {
    flashMessage('error', 'A discount code with this code already exists.');
    redirect('/admin/discount-codes/edit?id=' . $id);
}

DiscountCode::update($id, [
    'code' => $code,
    'type' => $type,
    'value' => $value,
    'min_order_dkk' => $minOrderDkk,
    'max_uses' => $maxUses,
    'applies_to' => $appliesTo,
    'expires_at' => $expiresAt,
    'status' => $status,
]);

logAudit('discount_code_updated', 'discount_code', $id);
flashMessage('success', 'Discount code updated successfully.');
redirect('/admin/discount-codes');
