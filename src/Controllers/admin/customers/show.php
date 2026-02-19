<?php

use App\Models\User;
use App\Models\Order;
use App\Models\EmailSignup;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$customer = User::find($id);

if (!$customer || $customer['tenant_id'] != $tenantId || $customer['role'] !== 'customer') {
    http_response_code(404);
    view('errors/404');
    exit;
}

$orders = Order::allByCustomer($id, $tenantId);
$signups = EmailSignup::allByTenant($tenantId);
// Filter signups to this customer's email
$signups = array_filter($signups, fn($s) => $s['email'] === $customer['email']);

view('admin/customers/show', [
    'tenant' => currentTenant(),
    'customer' => $customer,
    'orders' => $orders,
    'signups' => $signups,
]);
