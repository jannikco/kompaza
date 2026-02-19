<?php

use App\Auth\Auth;
use App\Models\Order;

Auth::requireCustomer();

$tenant = currentTenant();
$tenantId = currentTenantId();
$customer = Auth::user();

$recentOrders = Order::allByCustomer($customer['id'], $tenantId);
$recentOrders = array_slice($recentOrders, 0, 5);

view('shop/account/index', [
    'tenant' => $tenant,
    'customer' => $customer,
    'recentOrders' => $recentOrders,
]);
