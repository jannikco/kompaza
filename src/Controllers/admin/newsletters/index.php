<?php

use App\Models\Newsletter;

$tenantId = currentTenantId();
$newsletters = Newsletter::allByTenant($tenantId);

$pageTitle = 'Newsletters';
$currentPage = 'newsletters';

view('admin/newsletters/index', compact('newsletters', 'pageTitle', 'currentPage'));
