<?php

require_once CONTROLLERS_PATH . '/../Helpers/admin-layout.php';

use App\Auth\Auth;
use App\Models\Tenant;

$admin = Auth::admin();
$tenantId = $admin['tenant_id'] ?? null;
$tenant = $tenantId ? Tenant::find($tenantId) : null;

renderAdminPage('Betalinger', 'stripe-connect', 'admin/stripe-connect/index', compact('tenant'));
