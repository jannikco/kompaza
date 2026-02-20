<?php

use App\Models\CompanyAccount;
use App\Models\Course;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$company = CompanyAccount::find($id, $tenantId);

if (!$company) {
    http_response_code(404);
    view('errors/404');
    exit;
}

// Load customers for admin select
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("SELECT id, name, email FROM users WHERE tenant_id = ? AND role = 'customer' ORDER BY name");
$stmt->execute([$tenantId]);
$customers = $stmt->fetchAll();

// Load team members
$members = CompanyAccount::getTeamMembers($id);

// Load licenses
$licenses = CompanyAccount::getLicenses($id);

// Load courses for license add form
$courses = Course::allByTenant($tenantId);

view('admin/companies/edit', [
    'tenant' => currentTenant(),
    'company' => $company,
    'customers' => $customers,
    'members' => $members,
    'licenses' => $licenses,
    'courses' => $courses,
]);
