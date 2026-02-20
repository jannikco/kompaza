<?php

use App\Models\CompanyAccount;

if (!isPost()) redirect('/admin/companies');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/companies');
}

$companyId = $_POST['company_id'] ?? null;
$tenantId = currentTenantId();

if (!$companyId) redirect('/admin/companies');

$company = CompanyAccount::find($companyId, $tenantId);
if (!$company) {
    flashMessage('error', 'Company account not found.');
    redirect('/admin/companies');
}

$courseId = !empty($_POST['course_id']) ? (int)$_POST['course_id'] : null;
$seatsTotal = (int)($_POST['seats_total'] ?? 1);
$expiresAt = !empty($_POST['expires_at']) ? sanitize($_POST['expires_at']) : null;

if (!$courseId) {
    flashMessage('error', 'Course selection is required.');
    redirect('/admin/companies/edit?id=' . $companyId);
}

CompanyAccount::addLicense([
    'company_account_id' => $companyId,
    'course_id' => $courseId,
    'seats_total' => $seatsTotal,
    'seats_used' => 0,
    'expires_at' => $expiresAt,
]);

logAudit('license_added', 'company_account', $companyId, ['course_id' => $courseId]);
flashMessage('success', 'Course license added successfully.');
redirect('/admin/companies/edit?id=' . $companyId);
