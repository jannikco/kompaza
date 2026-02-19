<?php

use App\Auth\Auth;
use App\Models\Order;

Auth::requireCustomer();

$tenant = currentTenant();
$tenantId = currentTenantId();
$customer = Auth::user();

$orders = Order::allByCustomer($customer['id'], $tenantId);

view('shop/account/orders', [
    'tenant' => $tenant,
    'customer' => $customer,
    'orders' => $orders,
]);
