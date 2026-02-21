<?php

use App\Models\Redirect;

if (!isPost()) redirect('/admin/redirects');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Try again.');
    redirect('/admin/redirects');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/redirects');

$redirect = Redirect::find($id, $tenantId);
if (!$redirect) {
    flashMessage('error', 'Redirect not found.');
    redirect('/admin/redirects');
}

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
    redirect('/admin/redirects/edit?id=' . $id);
}

if (!in_array($statusCode, [301, 302])) {
    $statusCode = 301;
}

Redirect::update($id, [
    'from_path' => $fromPath,
    'to_path' => $toPath,
    'status_code' => $statusCode,
    'is_active' => $isActive ? 1 : 0,
]);

logAudit('redirect_updated', 'redirect', $id);
flashMessage('success', 'Redirect updated.');
redirect('/admin/redirects');
