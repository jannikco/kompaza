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

$email = sanitize($_POST['email'] ?? '');
$name = sanitize($_POST['name'] ?? '');

if (empty($email)) {
    flashMessage('error', 'Email is required.');
    redirect('/admin/companies/edit?id=' . $companyId);
}

// Check if user exists with this email
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND tenant_id = ?");
$stmt->execute([$email, $tenantId]);
$existingUser = $stmt->fetch();

CompanyAccount::addTeamMember([
    'company_account_id' => $companyId,
    'user_id' => $existingUser['id'] ?? null,
    'email' => $email,
    'name' => $name ?: null,
    'status' => $existingUser ? 'active' : 'invited',
]);

logAudit('team_member_added', 'company_account', $companyId, ['email' => $email]);
flashMessage('success', 'Team member added successfully.');
redirect('/admin/companies/edit?id=' . $companyId);
