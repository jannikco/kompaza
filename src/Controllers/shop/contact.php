<?php

$tenant = currentTenant();

view('shop/contact', [
    'tenant' => $tenant,
]);
