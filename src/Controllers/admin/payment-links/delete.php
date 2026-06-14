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

PaymentLink::delete($id, $tenantId);

logAudit('payment_link_deleted', 'payment_link', $id);
flashMessage('success', 'Payment link deleted.');
redirect('/admin/payment-links');
