<?php

use App\Models\CompanyAccount;

if (!isPost()) redirect('/admin/companies');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/companies/create');
}

$tenantId = currentTenantId();

$companyName = sanitize($_POST['company_name'] ?? '');
$adminUserId = !empty($_POST['admin_user_id']) ? (int)$_POST['admin_user_id'] : null;
$totalLicenses = (int)($_POST['total_licenses'] ?? 0);

if (empty($companyName)) {
    flashMessage('error', 'Company name is required.');
    redirect('/admin/companies/create');
}

$companyId = CompanyAccount::create([
    'tenant_id' => $tenantId,
    'company_name' => $companyName,
    'admin_user_id' => $adminUserId,
    'total_licenses' => $totalLicenses,
    'status' => 'active',
]);

logAudit('company_created', 'company_account', $companyId);
flashMessage('success', 'Company account created successfully.');
redirect('/admin/companies');
