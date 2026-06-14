<?php

use App\Models\AbandonedCart;

$tenantId = currentTenantId();
$status = sanitize($_GET['status'] ?? '');
$carts = AbandonedCart::allByTenant($tenantId, $status ?: null);
$activeCount = AbandonedCart::countByTenant($tenantId, 'active');
$recoveredCount = AbandonedCart::countByTenant($tenantId, 'recovered');
$recoveredRevenue = AbandonedCart::totalRecoveredRevenue($tenantId);

view('admin/abandoned-carts/index', [
    'tenant' => currentTenant(),
    'carts' => $carts,
    'activeCount' => $activeCount,
    'recoveredCount' => $recoveredCount,
    'recoveredRevenue' => $recoveredRevenue,
    'currentStatus' => $status,
]);
