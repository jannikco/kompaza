<?php

$tenant = currentTenant();

view('shop/privacy-policy', [
    'tenant' => $tenant,
]);
