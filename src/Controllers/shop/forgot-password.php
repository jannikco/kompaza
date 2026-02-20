<?php

$tenant = currentTenant();

view('shop/forgot-password', [
    'tenant' => $tenant,
]);
