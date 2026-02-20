<?php

require_once CONTROLLERS_PATH . '/../Helpers/admin-layout.php';

use App\Auth\Auth;
use App\Models\EbookPurchase;

$admin = Auth::admin();
$tenantId = $admin['tenant_id'] ?? null;

$purchases = $tenantId ? EbookPurchase::allByTenantId($tenantId) : [];
$totalSales = $tenantId ? EbookPurchase::countByTenantId($tenantId) : 0;
$totalRevenue = $tenantId ? EbookPurchase::revenueByTenantId($tenantId) : 0;

renderAdminPage('Salg', 'sales', 'admin/sales/index', compact(
    'purchases', 'totalSales', 'totalRevenue'
));
