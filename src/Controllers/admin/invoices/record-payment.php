<?php

use App\Auth\Auth;
use App\Models\Invoice;

Auth::requireTenantAdmin();

$tenantId = currentTenantId();

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/admin/invoices');
}

$id = (int)($_POST['invoice_id'] ?? 0);
$invoice = Invoice::find($id, $tenantId);
if (!$invoice) {
    flashMessage('error', 'Invoice not found.');
    redirect('/admin/invoices');
}

$amount = (float)($_POST['amount_dkk'] ?? 0);
if ($amount <= 0) {
    flashMessage('error', 'Payment amount must be greater than zero.');
    redirect('/admin/invoices/edit?id=' . $id);
}

$remaining = (float)$invoice['total_dkk'] - (float)$invoice['amount_paid_dkk'];
if ($amount > $remaining) {
    $amount = $remaining;
}

Invoice::recordPayment(
    $id,
    $amount,
    sanitize($_POST['payment_method'] ?? 'bank_transfer'),
    sanitize($_POST['payment_reference'] ?? ''),
    sanitize($_POST['notes'] ?? ''),
    currentUserId()
);

logAudit('invoice_payment_recorded', 'invoice', $id, ['amount' => $amount]);

flashMessage('success', 'Payment of ' . formatMoney($amount) . ' recorded.');
redirect('/admin/invoices/edit?id=' . $id);
