<?php

use App\Models\CompanyAccount;

if (!isPost()) redirect('/admin/companies');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/companies');
}

$licenseId = $_POST['license_id'] ?? null;
$companyId = $_POST['company_id'] ?? null;
$tenantId = currentTenantId();

if (!$licenseId || !$companyId) redirect('/admin/companies');

$company = CompanyAccount::find($companyId, $tenantId);
if (!$company) {
    flashMessage('error', 'Company account not found.');
    redirect('/admin/companies');
}

$license = CompanyAccount::findLicense($licenseId);
if (!$license || $license['company_account_id'] != $companyId) {
    flashMessage('error', 'License not found.');
    redirect('/admin/companies/edit?id=' . $companyId);
}

CompanyAccount::removeLicense($licenseId);

logAudit('license_removed', 'company_account', $companyId, ['license_id' => $licenseId]);
flashMessage('success', 'Course license removed successfully.');
redirect('/admin/companies/edit?id=' . $companyId);
