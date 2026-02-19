<?php

$tenant = currentTenant();

view('admin/settings/index', [
    'tenant' => $tenant,
]);
