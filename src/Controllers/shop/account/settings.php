<?php

use App\Auth\Auth;

Auth::requireCustomer();

$tenant = currentTenant();
$customer = Auth::user();

view('shop/account/settings', [
    'tenant' => $tenant,
    'customer' => $customer,
]);
