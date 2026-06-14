<?php

// Set a user's status to active / inactive / banned.
use App\Models\User;

if (!isPost()) {
    redirect('/users');
}

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/users');
}

$id = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';

$allowedStatuses = ['active', 'inactive', 'banned'];
if (!in_array($status, $allowedStatuses, true)) {
    flashMessage('error', 'Invalid status.');
    redirect('/users');
}

$user = User::find($id);
if (!$user) {
    flashMessage('error', 'User not found.');
    redirect('/users');
}

// Guard: don't lock yourself out by suspending your own account.
if ((int)$id === (int)currentUserId() && $status !== 'active') {
    flashMessage('error', 'You cannot deactivate your own account.');
    redirect('/users');
}

// Guard: keep at least one active superadmin on the platform.
if ($user['role'] === 'superadmin' && $status !== 'active') {
    $db = \App\Database\Database::getConnection();
    $countStmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'superadmin' AND status = 'active'");
    if ((int)$countStmt->fetchColumn() <= 1) {
        flashMessage('error', 'Cannot deactivate the last active superadmin.');
        redirect('/users');
    }
}

User::update($id, ['status' => $status]);

logAudit('user_status_changed', 'user', $id, ['status' => $status]);
flashMessage('success', 'User status updated to ' . $status . '.');
redirect('/users');
