<?php

use App\Auth\Auth;

$tenant = currentTenant();

// If already authenticated, redirect
if (Auth::check()) {
    if (Auth::isCustomer()) {
        redirect('/konto');
    } elseif (Auth::isTenantAdmin()) {
        redirect('/admin');
    }
}

view('shop/register', [
    'tenant' => $tenant,
]);
