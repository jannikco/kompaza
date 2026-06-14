<?php

use App\Models\PageView;

$tenant = currentTenant();
$tenantId = currentTenantId();

$period = in_array($_GET['period'] ?? '', ['7', '30', '90']) ? (int)$_GET['period'] : 30;

// Key metrics
$mrr = PageView::getMRR($tenantId);
$clv = PageView::getCLV($tenantId);
$churnRate = PageView::getChurnRate($tenantId);

// Revenue by month (last 12 months)
$revenueByMonth = PageView::getRevenueByMonth($tenantId, 12);

// Revenue by product (top 10)
$revenueByProduct = PageView::getRevenueByProduct($tenantId, 10);

// Conversion funnel
$funnel = PageView::getConversionFunnel($tenantId, $period);

// Top pages
$topPages = PageView::getTopPages($tenantId, $period, 10);

// Traffic sources
$trafficSources = PageView::getTrafficSources($tenantId, $period);

// Daily growth
$dailyGrowth = PageView::getDailyGrowth($tenantId, $period);

view('admin/analytics/index', [
    'tenant' => $tenant,
    'period' => $period,
    'mrr' => $mrr,
    'clv' => $clv,
    'churnRate' => $churnRate,
    'revenueByMonth' => $revenueByMonth,
    'revenueByProduct' => $revenueByProduct,
    'funnel' => $funnel,
    'topPages' => $topPages,
    'trafficSources' => $trafficSources,
    'dailyGrowth' => $dailyGrowth,
]);
