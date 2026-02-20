<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\InvoiceService;

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/ordrer');
}

$tenantId = currentTenantId();
$orderId = (int)($_POST['order_id'] ?? 0);

$order = Order::find($orderId, $tenantId);
if (!$order) {
    flashMessage('error', 'Order not found.');
    redirect('/admin/ordrer');
}

// Generate invoice number and due date
$invoiceNumber = InvoiceService::generateInvoiceNumber($tenantId);
$dueDate = InvoiceService::generateDueDate(14);

// Update order with invoice info
Order::update($orderId, [
    'invoice_number' => $invoiceNumber,
    'invoice_due_date' => $dueDate,
]);

// Generate and save invoice HTML
$order['invoice_number'] = $invoiceNumber;
$order['invoice_due_date'] = $dueDate;
$items = OrderItem::allByOrder($orderId);
$tenant = currentTenant();
$invoicePath = InvoiceService::saveInvoicePdf($order, $items, $tenant);

Order::update($orderId, ['invoice_pdf_path' => $invoicePath]);

logAudit('invoice_generated', 'order', $orderId, ['invoice_number' => $invoiceNumber]);
flashMessage('success', 'Invoice ' . $invoiceNumber . ' generated successfully.');
redirect('/admin/ordrer/vis?id=' . $orderId);
