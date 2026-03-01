<?php

use App\Models\PostAutomation;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$automation = PostAutomation::find($id, $tenantId);

if (!$automation) {
    http_response_code(404);
    view('errors/404');
    exit;
}

// Load LinkedIn accounts for dropdown
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("SELECT id, linkedin_name, linkedin_email, status FROM linkedin_accounts WHERE tenant_id = ?");
$stmt->execute([$tenantId]);
$linkedinAccounts = $stmt->fetchAll();

// Load lead magnets for dropdown
$stmt = $db->prepare("SELECT id, title, slug FROM lead_magnets WHERE tenant_id = ? AND status = 'published' ORDER BY title ASC");
$stmt->execute([$tenantId]);
$leadMagnets = $stmt->fetchAll();

view('admin/connectpilot/automations/edit', [
    'tenant' => currentTenant(),
    'automation' => $automation,
    'linkedinAccounts' => $linkedinAccounts,
    'leadMagnets' => $leadMagnets,
]);
