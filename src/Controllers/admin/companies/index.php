<?php

use App\Models\CompanyAccount;

$tenantId = currentTenantId();
$companies = CompanyAccount::allByTenant($tenantId);

view('admin/companies/index', [
    'tenant' => currentTenant(),
    'companies' => $companies,
]);
