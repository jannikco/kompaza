<?php

use App\Auth\Auth;
use App\Models\Order;
use App\Models\OrderItem;

Auth::requireCustomer();

$tenant = currentTenant();
$tenantId = currentTenantId();
$customer = Auth::user();

$order = Order::find($id, $tenantId);

if (!$order) {
    http_response_code(404);
    view('errors/404');
    exit;
}

// Verify the order belongs to the authenticated customer
if ((int)$order['customer_id'] !== (int)$customer['id']) {
    http_response_code(403);
    view('errors/404');
    exit;
}

$orderItems = OrderItem::allByOrder($order['id']);

view('shop/account/order-detail', [
    'tenant' => $tenant,
    'customer' => $customer,
    'order' => $order,
    'orderItems' => $orderItems,
]);
