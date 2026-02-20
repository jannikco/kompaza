<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\InvoiceService;

$tenantId = currentTenantId();
$orderId = (int)($_GET['id'] ?? 0);

$order = Order::find($orderId, $tenantId);
if (!$order) {
    flashMessage('error', 'Order not found.');
    redirect('/admin/ordrer');
}

// If invoice file exists, serve it
if (!empty($order['invoice_pdf_path'])) {
    $path = STORAGE_PATH . '/' . $order['invoice_pdf_path'];
    if (file_exists($path)) {
        header('Content-Type: text/html; charset=UTF-8');
        readfile($path);
        exit;
    }
}

// Otherwise, generate on the fly
$items = OrderItem::allByOrder($orderId);
$tenant = currentTenant();
$html = InvoiceService::generateInvoiceHtml($order, $items, $tenant);

header('Content-Type: text/html; charset=UTF-8');
echo $html;
exit;
