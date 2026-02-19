<?php

use App\Auth\Auth;

$tenant = currentTenant();

// If already authenticated as customer, redirect to account
if (Auth::check() && Auth::isCustomer()) {
    redirect('/konto');
}

view('shop/login', [
    'tenant' => $tenant,
]);
