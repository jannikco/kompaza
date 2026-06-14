<?php

// Reset a user's password to a new value.
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

$newPassword = $_POST['new_password'] ?? '';

if (strlen($newPassword) < 8) {
    flashMessage('error', 'New password must be at least 8 characters.');
    redirect('/users/edit?id=' . $id);
}

User::updatePassword($id, $newPassword);

logAudit('user_password_reset', 'user', $id);
flashMessage('success', 'Password updated successfully.');
redirect('/users/edit?id=' . $id);
