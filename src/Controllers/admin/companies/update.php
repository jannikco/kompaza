<?php

use App\Models\CompanyAccount;

if (!isPost()) redirect('/admin/companies');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/companies');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/companies');

$company = CompanyAccount::find($id, $tenantId);
if (!$company) {
    flashMessage('error', 'Company account not found.');
    redirect('/admin/companies');
}

$companyName = sanitize($_POST['company_name'] ?? '');
$adminUserId = !empty($_POST['admin_user_id']) ? (int)$_POST['admin_user_id'] : null;
$totalLicenses = (int)($_POST['total_licenses'] ?? 0);
$status = sanitize($_POST['status'] ?? 'active');

if (empty($companyName)) {
    flashMessage('error', 'Company name is required.');
    redirect('/admin/companies/edit?id=' . $id);
}

CompanyAccount::update($id, [
    'company_name' => $companyName,
    'admin_user_id' => $adminUserId,
    'total_licenses' => $totalLicenses,
    'status' => $status,
]);

logAudit('company_updated', 'company_account', $id);
flashMessage('success', 'Company account updated successfully.');
redirect('/admin/companies/edit?id=' . $id);
