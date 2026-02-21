<?php

use App\Models\Redirect;

if (!isPost()) redirect('/admin/redirects');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Try again.');
    redirect('/admin/redirects/create');
}

$tenantId = currentTenantId();

$fromPath = trim($_POST['from_path'] ?? '');
$toPath = trim($_POST['to_path'] ?? '');
$statusCode = (int)($_POST['status_code'] ?? 301);
$isActive = !empty($_POST['is_active']);

// Ensure from_path starts with /
if ($fromPath && $fromPath[0] !== '/') {
    $fromPath = '/' . $fromPath;
}
// Remove trailing slash (unless it's just /)
if ($fromPath !== '/' && str_ends_with($fromPath, '/')) {
    $fromPath = rtrim($fromPath, '/');
}

if (!$fromPath || !$toPath) {
    flashMessage('error', 'Both "From Path" and "To Path" are required.');
    redirect('/admin/redirects/create');
}

if (!in_array($statusCode, [301, 302])) {
    $statusCode = 301;
}

// Check for duplicate
$existing = Redirect::findByPath($fromPath, $tenantId);
if ($existing) {
    flashMessage('error', 'A redirect for this path already exists.');
    redirect('/admin/redirects/create');
}

$id = Redirect::create([
    'tenant_id' => $tenantId,
    'from_path' => $fromPath,
    'to_path' => $toPath,
    'status_code' => $statusCode,
    'is_active' => $isActive ? 1 : 0,
]);

logAudit('redirect_created', 'redirect', $id);
flashMessage('success', 'Redirect created.');
redirect('/admin/redirects');
