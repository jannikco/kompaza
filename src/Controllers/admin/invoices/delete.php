<?php

use App\Auth\Auth;
use App\Models\Invoice;

Auth::requireTenantAdmin();

$tenantId = currentTenantId();

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/admin/invoices');
}

$id = (int)($_POST['id'] ?? 0);
$invoice = Invoice::find($id, $tenantId);
if (!$invoice) {
    flashMessage('error', 'Invoice not found.');
    redirect('/admin/invoices');
}

if ($invoice['status'] !== 'draft') {
    flashMessage('error', 'Only draft invoices can be deleted.');
    redirect('/admin/invoices');
}

Invoice::delete($id, $tenantId);
logAudit('invoice_deleted', 'invoice', $id, ['invoice_number' => $invoice['invoice_number']]);

flashMessage('success', 'Invoice deleted.');
redirect('/admin/invoices');
