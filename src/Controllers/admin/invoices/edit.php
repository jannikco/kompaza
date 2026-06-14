<?php

use App\Auth\Auth;
use App\Models\Invoice;
use App\Models\InvoiceItem;

Auth::requireTenantAdmin();

$tenantId = currentTenantId();
$id = (int)($_GET['id'] ?? 0);

$invoice = Invoice::find($id, $tenantId);
if (!$invoice) {
    flashMessage('error', 'Invoice not found.');
    redirect('/admin/invoices');
}

$items = InvoiceItem::allByInvoice($id);

view('admin/invoices/form', [
    'invoice' => $invoice,
    'invoiceNumber' => $invoice['invoice_number'],
    'customer' => null,
    'items' => $items,
]);
