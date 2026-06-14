<?php

// Create a new platform superadmin (role=superadmin, tenant_id NULL).
use App\Models\User;

if (!isPost()) {
    redirect('/users');
}

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/users/create');
}

$name = sanitize($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if ($name === '' || $email === '' || $password === '') {
    flashMessage('error', 'Name, email and password are all required.');
    redirect('/users/create');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flashMessage('error', 'Please enter a valid email address.');
    redirect('/users/create');
}

if (strlen($password) < 8) {
    flashMessage('error', 'Password must be at least 8 characters.');
    redirect('/users/create');
}

// Email must be unique among superadmins (tenant_id IS NULL).
$db = \App\Database\Database::getConnection();
$check = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND tenant_id IS NULL");
$check->execute([$email]);
if ((int)$check->fetchColumn() > 0) {
    flashMessage('error', 'A superadmin with that email already exists.');
    redirect('/users/create');
}

$id = User::create([
    'tenant_id' => null,
    'role' => 'superadmin',
    'name' => $name,
    'email' => $email,
    'password' => $password,
    'status' => 'active',
]);

logAudit('superadmin_created', 'user', $id, ['email' => $email]);
flashMessage('success', 'Superadmin created successfully.');
redirect('/users');
