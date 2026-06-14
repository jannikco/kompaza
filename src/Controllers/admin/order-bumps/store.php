<?php

use App\Models\OrderBump;

if (!isPost()) redirect('/admin/order-bumps');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/order-bumps/create');
}

$tenantId = currentTenantId();
$name = sanitize($_POST['name'] ?? '');

if (!$name) {
    flashMessage('error', 'Name is required.');
    redirect('/admin/order-bumps/create');
}

$id = OrderBump::create([
    'tenant_id' => $tenantId,
    'name' => $name,
    'description' => sanitize($_POST['description'] ?? ''),
    'product_id' => (int)($_POST['product_id'] ?? 0),
    'bump_price_dkk' => (float)($_POST['bump_price_dkk'] ?? 0),
    'display_text' => sanitize($_POST['display_text'] ?? ''),
    'applies_to' => sanitize($_POST['applies_to'] ?? 'all'),
    'applies_to_value' => !empty($_POST['applies_to_value']) ? json_encode(array_map('intval', explode(',', $_POST['applies_to_value']))) : null,
    'sort_order' => (int)($_POST['sort_order'] ?? 0),
    'status' => sanitize($_POST['status'] ?? 'active'),
]);

logAudit('order_bump_created', 'order_bump', $id);
flashMessage('success', 'Order bump created.');
redirect('/admin/order-bumps');
