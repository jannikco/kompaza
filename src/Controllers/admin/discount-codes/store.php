<?php

use App\Models\DiscountCode;

if (!isPost()) redirect('/admin/discount-codes');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/discount-codes/create');
}

$tenantId = currentTenantId();

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
    redirect('/admin/discount-codes/create');
}

if ($value <= 0) {
    flashMessage('error', 'Discount value must be greater than zero.');
    redirect('/admin/discount-codes/create');
}

if ($type === 'percentage' && $value > 100) {
    flashMessage('error', 'Percentage discount cannot exceed 100%.');
    redirect('/admin/discount-codes/create');
}

if (!in_array($type, ['percentage', 'fixed'])) {
    flashMessage('error', 'Invalid discount type.');
    redirect('/admin/discount-codes/create');
}

if (!in_array($appliesTo, ['all', 'courses', 'products', 'ebooks'])) {
    flashMessage('error', 'Invalid "applies to" value.');
    redirect('/admin/discount-codes/create');
}

if (!in_array($status, ['active', 'inactive'])) {
    flashMessage('error', 'Invalid status.');
    redirect('/admin/discount-codes/create');
}

// Check for duplicate code
$existing = DiscountCode::findByCode($code, $tenantId);
if ($existing) {
    flashMessage('error', 'A discount code with this code already exists.');
    redirect('/admin/discount-codes/create');
}

$discountCodeId = DiscountCode::create([
    'tenant_id' => $tenantId,
    'code' => $code,
    'type' => $type,
    'value' => $value,
    'min_order_dkk' => $minOrderDkk,
    'max_uses' => $maxUses,
    'applies_to' => $appliesTo,
    'expires_at' => $expiresAt,
    'status' => $status,
]);

logAudit('discount_code_created', 'discount_code', $discountCodeId);
flashMessage('success', 'Discount code created successfully.');
redirect('/admin/discount-codes');
