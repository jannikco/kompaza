<?php

$tenant = currentTenant();

// Settings view historically expected $settings; values live on the tenant row.
// Pass both so branding/integration fields always load correctly.
view('admin/settings/index', [
    'tenant' => $tenant,
    'settings' => $tenant,
]);
