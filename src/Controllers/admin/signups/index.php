<?php

use App\Models\EmailSignup;

$tenantId = currentTenantId();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

$signups = EmailSignup::allByTenant($tenantId, $perPage, $offset);
$totalSignups = EmailSignup::countByTenant($tenantId);
$totalPages = ceil($totalSignups / $perPage);

view('admin/signups/index', [
    'tenant' => currentTenant(),
    'signups' => $signups,
    'page' => $page,
    'totalPages' => $totalPages,
    'totalSignups' => $totalSignups,
]);
