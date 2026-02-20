<?php

use App\Models\CompanyAccount;

if (!isPost()) redirect('/admin/companies');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/companies');
}

$memberId = $_POST['member_id'] ?? null;
$companyId = $_POST['company_id'] ?? null;
$tenantId = currentTenantId();

if (!$memberId || !$companyId) redirect('/admin/companies');

$company = CompanyAccount::find($companyId, $tenantId);
if (!$company) {
    flashMessage('error', 'Company account not found.');
    redirect('/admin/companies');
}

$member = CompanyAccount::findTeamMember($memberId);
if (!$member || $member['company_account_id'] != $companyId) {
    flashMessage('error', 'Team member not found.');
    redirect('/admin/companies/edit?id=' . $companyId);
}

CompanyAccount::removeTeamMember($memberId);

logAudit('team_member_removed', 'company_account', $companyId, ['member_id' => $memberId]);
flashMessage('success', 'Team member removed successfully.');
redirect('/admin/companies/edit?id=' . $companyId);
