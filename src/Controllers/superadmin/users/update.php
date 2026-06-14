<?php

// Update name, email, role, status and tenant_id for any platform user.
use App\Models\User;

if (!isPost()) {
    redirect('/users');
}

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/users');
}

$id = (int)($_POST['id'] ?? 0);
$user = User::find($id);
if (!$user) {
    flashMessage('error', 'User not found.');
    redirect('/users');
}

$name = sanitize($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$role = $_POST['role'] ?? '';
$status = $_POST['status'] ?? '';
$tenantId = ($_POST['tenant_id'] ?? '') !== '' ? (int)$_POST['tenant_id'] : null;

$allowedRoles = ['superadmin', 'tenant_admin', 'customer'];
$allowedStatuses = ['active', 'inactive', 'banned'];

if ($name === '' || $email === '') {
    flashMessage('error', 'Name and email are required.');
    redirect('/users/edit?id=' . $id);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flashMessage('error', 'Please enter a valid email address.');
    redirect('/users/edit?id=' . $id);
}

if (!in_array($role, $allowedRoles, true)) {
    flashMessage('error', 'Invalid role.');
    redirect('/users/edit?id=' . $id);
}

if (!in_array($status, $allowedStatuses, true)) {
    flashMessage('error', 'Invalid status.');
    redirect('/users/edit?id=' . $id);
}

// Superadmins must not be tied to a tenant; tenant_admin/customer must be.
if ($role === 'superadmin') {
    $tenantId = null;
} elseif ($tenantId === null) {
    flashMessage('error', 'A tenant is required for tenant admins and customers.');
    redirect('/users/edit?id=' . $id);
}

$db = \App\Database\Database::getConnection();

// Validate tenant exists when provided.
if ($tenantId !== null) {
    $tCheck = $db->prepare("SELECT COUNT(*) FROM tenants WHERE id = ?");
    $tCheck->execute([$tenantId]);
    if ((int)$tCheck->fetchColumn() === 0) {
        flashMessage('error', 'Selected tenant does not exist.');
        redirect('/users/edit?id=' . $id);
    }
}

// Email uniqueness within the same tenant scope (matches the unique_email_tenant key).
if ($tenantId === null) {
    $dup = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND tenant_id IS NULL AND id != ?");
    $dup->execute([$email, $id]);
} else {
    $dup = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND tenant_id = ? AND id != ?");
    $dup->execute([$email, $tenantId, $id]);
}
if ((int)$dup->fetchColumn() > 0) {
    flashMessage('error', 'Another user already uses that email in this scope.');
    redirect('/users/edit?id=' . $id);
}

// Guard: don't let the last superadmin be demoted/de-scoped away.
if ($user['role'] === 'superadmin' && $role !== 'superadmin') {
    $countStmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'superadmin'");
    if ((int)$countStmt->fetchColumn() <= 1) {
        flashMessage('error', 'Cannot change the role of the last superadmin.');
        redirect('/users/edit?id=' . $id);
    }
}

$stmt = $db->prepare("UPDATE users SET name = ?, email = ?, role = ?, status = ?, tenant_id = ? WHERE id = ?");
$stmt->execute([$name, $email, $role, $status, $tenantId, $id]);

logAudit('user_updated', 'user', $id, ['role' => $role, 'status' => $status, 'tenant_id' => $tenantId]);
flashMessage('success', 'User updated successfully.');
redirect('/users');
