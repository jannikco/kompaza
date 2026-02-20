<?php

use App\Models\Order;

$tenantId = currentTenantId();
$tenant = currentTenant();

// Fetch all orders (high limit for export)
$orders = Order::allByTenant($tenantId, null, 100000, 0);

$filename = 'orders_' . ($tenant['slug'] ?? 'export') . '_' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// BOM for Excel UTF-8 compatibility
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Header row
fputcsv($output, [
    'Order Number',
    'Customer Name',
    'Customer Email',
    'Subtotal',
    'Tax',
    'Shipping',
    'Discount',
    'Total',
    'Payment Method',
    'Status',
    'Date',
], ';');

foreach ($orders as $order) {
    fputcsv($output, [
        $order['order_number'] ?? '',
        $order['customer_name'] ?? '',
        $order['customer_email'] ?? '',
        number_format((float)($order['subtotal_dkk'] ?? 0), 2, '.', ''),
        number_format((float)($order['tax_dkk'] ?? 0), 2, '.', ''),
        number_format((float)($order['shipping_dkk'] ?? 0), 2, '.', ''),
        number_format((float)($order['discount_dkk'] ?? 0), 2, '.', ''),
        number_format((float)($order['total_dkk'] ?? 0), 2, '.', ''),
        $order['payment_method'] ?? '',
        $order['status'] ?? '',
        formatDate($order['created_at'], 'd-m-Y H:i'),
    ], ';');
}

fclose($output);
exit;
