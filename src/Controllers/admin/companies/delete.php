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

CompanyAccount::delete($id, $tenantId);

logAudit('company_deleted', 'company_account', $id);
flashMessage('success', 'Company account deleted successfully.');
redirect('/admin/companies');
