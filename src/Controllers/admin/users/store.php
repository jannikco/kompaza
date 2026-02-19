<?php

use App\Models\User;

if (!isPost()) redirect('/admin/users');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/users/create');
}

$tenantId = currentTenantId();

$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$name || !$email || !$password) {
    flashMessage('error', 'Navn, email og adgangskode er påkrævet.');
    redirect('/admin/users/create');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flashMessage('error', 'Ugyldig email-adresse.');
    redirect('/admin/users/create');
}

if (strlen($password) < 8) {
    flashMessage('error', 'Adgangskoden skal være mindst 8 tegn.');
    redirect('/admin/users/create');
}

// Check if email already exists for this tenant
if (User::emailExistsForTenant($email, $tenantId)) {
    flashMessage('error', 'En bruger med denne email eksisterer allerede.');
    redirect('/admin/users/create');
}

$id = User::create([
    'tenant_id' => $tenantId,
    'role' => 'tenant_admin',
    'name' => $name,
    'email' => $email,
    'password' => $password,
    'phone' => sanitize($_POST['phone'] ?? ''),
    'status' => 'active',
]);

logAudit('user_created', 'user', $id);
flashMessage('success', 'Bruger oprettet.');
redirect('/admin/users');
