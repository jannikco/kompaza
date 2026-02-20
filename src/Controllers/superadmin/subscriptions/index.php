<?php

require_once CONTROLLERS_PATH . '/../Helpers/superadmin-layout.php';

use App\Models\TenantSubscription;

$subscriptions = TenantSubscription::all();
$statusCounts = TenantSubscription::countByStatus();

renderSuperadminPage('Abonnementer', 'subscriptions', 'superadmin/subscriptions/index', compact(
    'subscriptions', 'statusCounts'
));
