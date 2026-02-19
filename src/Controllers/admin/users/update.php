<?php

use App\Models\User;

if (!isPost()) redirect('/admin/users');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/users');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/users');

$user = User::find($id);
if (!$user || $user['tenant_id'] != $tenantId || $user['role'] !== 'tenant_admin') {
    flashMessage('error', 'Bruger ikke fundet.');
    redirect('/admin/users');
}

$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');

if (!$name || !$email) {
    flashMessage('error', 'Navn og email er påkrævet.');
    redirect('/admin/users/edit?id=' . $id);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flashMessage('error', 'Ugyldig email-adresse.');
    redirect('/admin/users/edit?id=' . $id);
}

// Check email uniqueness (exclude current user)
if (User::emailExistsForTenant($email, $tenantId, $id)) {
    flashMessage('error', 'En anden bruger med denne email eksisterer allerede.');
    redirect('/admin/users/edit?id=' . $id);
}

$data = [
    'name' => $name,
    'email' => $email,
    'phone' => sanitize($_POST['phone'] ?? ''),
    'status' => sanitize($_POST['status'] ?? 'active'),
];

User::update($id, $data);

// Update password if provided
$password = $_POST['password'] ?? '';
if ($password) {
    if (strlen($password) < 8) {
        flashMessage('error', 'Adgangskoden skal være mindst 8 tegn.');
        redirect('/admin/users/edit?id=' . $id);
    }
    User::updatePassword($id, $password);
}

logAudit('user_updated', 'user', $id);
flashMessage('success', 'Bruger opdateret.');
redirect('/admin/users');
