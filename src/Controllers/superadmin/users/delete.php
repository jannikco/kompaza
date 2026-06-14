<?php

// Delete a platform user with guards against self-deletion and removing the last superadmin.
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

// Guard: cannot delete yourself.
if ((int)$id === (int)currentUserId()) {
    flashMessage('error', 'You cannot delete your own account.');
    redirect('/users');
}

// Guard: cannot delete the last superadmin.
if ($user['role'] === 'superadmin') {
    $db = \App\Database\Database::getConnection();
    $countStmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'superadmin'");
    if ((int)$countStmt->fetchColumn() <= 1) {
        flashMessage('error', 'Cannot delete the last superadmin.');
        redirect('/users');
    }
}

User::delete($id);

logAudit('user_deleted', 'user', $id, ['email' => $user['email'], 'role' => $user['role']]);
flashMessage('success', 'User deleted successfully.');
redirect('/users');
