<?php

use App\Database\Database;

if (!isPost()) {
    redirect('/billing/invoices');
}

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/billing/invoices');
}

$id = (int) sanitize($_POST['id'] ?? 0);

$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM subscription_invoices WHERE id = ?");
$stmt->execute([$id]);
$invoice = $stmt->fetch();

if (!$invoice) {
    flashMessage('error', 'Invoice not found.');
    redirect('/billing/invoices');
}

if ($invoice['status'] === 'paid') {
    flashMessage('error', 'Invoice is already marked as paid.');
    redirect('/billing/invoices');
}

// Mark the invoice paid (manual reconciliation).
$update = $db->prepare("UPDATE subscription_invoices SET status = 'paid', paid_at = ? WHERE id = ?");
$update->execute([date('Y-m-d H:i:s'), $id]);

logAudit('invoice_marked_paid', 'subscription_invoice', $id, [
    'tenant_id'    => (int) $invoice['tenant_id'],
    'amount_cents' => (int) $invoice['amount_cents'],
]);

flashMessage('success', 'Invoice marked as paid.');
redirect('/billing/invoices');
