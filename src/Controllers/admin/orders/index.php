<?php

use App\Models\Order;

$tenantId = currentTenantId();
$status = sanitize($_GET['status'] ?? '');
$orders = Order::allByTenant($tenantId, $status ?: null);

view('admin/orders/index', [
    'tenant' => currentTenant(),
    'orders' => $orders,
    'currentStatus' => $status,
]);
