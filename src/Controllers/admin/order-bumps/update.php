<?php

use App\Models\OrderBump;

if (!isPost()) redirect('/admin/order-bumps');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/order-bumps');
}

$id = (int)($_POST['id'] ?? 0);
$tenantId = currentTenantId();

$bump = OrderBump::find($id, $tenantId);
if (!$bump) {
    flashMessage('error', 'Order bump not found.');
    redirect('/admin/order-bumps');
}

$name = sanitize($_POST['name'] ?? '');
if (!$name) {
    flashMessage('error', 'Name is required.');
    redirect('/admin/order-bumps/edit?id=' . $id);
}

OrderBump::update($id, [
    'name' => $name,
    'description' => sanitize($_POST['description'] ?? ''),
    'product_id' => (int)($_POST['product_id'] ?? 0),
    'bump_price_dkk' => (float)($_POST['bump_price_dkk'] ?? 0),
    'display_text' => sanitize($_POST['display_text'] ?? ''),
    'applies_to' => sanitize($_POST['applies_to'] ?? 'all'),
    'applies_to_value' => !empty($_POST['applies_to_value']) ? json_encode(array_map('intval', explode(',', $_POST['applies_to_value']))) : null,
    'sort_order' => (int)($_POST['sort_order'] ?? 0),
    'status' => sanitize($_POST['status'] ?? 'active'),
], $tenantId);

logAudit('order_bump_updated', 'order_bump', $id);
flashMessage('success', 'Order bump updated.');
redirect('/admin/order-bumps');
