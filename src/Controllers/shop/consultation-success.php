<?php

$tenant = currentTenant();
$bookingRef = sanitize($_GET['ref'] ?? '');

view('shop/consultation-success', [
    'tenant' => $tenant,
    'bookingRef' => $bookingRef,
]);
