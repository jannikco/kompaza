<?php

use App\Models\PaymentLink;

if (!isPost()) redirect('/admin/payment-links');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/payment-links');
}

$id = (int)($_POST['id'] ?? 0);
$tenantId = currentTenantId();

$link = PaymentLink::find($id, $tenantId);
if (!$link) {
    flashMessage('error', 'Payment link not found.');
    redirect('/admin/payment-links');
}

$name = sanitize($_POST['name'] ?? '');
if (!$name) {
    flashMessage('error', 'Name is required.');
    redirect('/admin/payment-links/edit?id=' . $id);
}

PaymentLink::update($id, [
    'name' => $name,
    'product_id' => (int)($_POST['product_id'] ?? 0),
    'custom_price_dkk' => !empty($_POST['custom_price_dkk']) ? (float)$_POST['custom_price_dkk'] : null,
    'custom_name' => sanitize($_POST['custom_name'] ?? '') ?: null,
    'allow_quantity' => isset($_POST['allow_quantity']) ? 1 : 0,
    'max_uses' => !empty($_POST['max_uses']) ? (int)$_POST['max_uses'] : null,
    'expires_at' => !empty($_POST['expires_at']) ? $_POST['expires_at'] : null,
    'redirect_url' => sanitize($_POST['redirect_url'] ?? '') ?: null,
    'status' => sanitize($_POST['status'] ?? 'active'),
]);

logAudit('payment_link_updated', 'payment_link', $id);
flashMessage('success', 'Payment link updated.');
redirect('/admin/payment-links');
