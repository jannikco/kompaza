<?php

$tenant = currentTenant();

view('shop/terms-of-service', [
    'tenant' => $tenant,
]);
