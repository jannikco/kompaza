<?php

use App\Auth\Auth;
use App\Models\User;

$tenant = currentTenant();
$tenantId = currentTenantId();

// Verify CSRF
$csrfToken = $_POST[CSRF_TOKEN_NAME] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    flashMessage('error', 'Ugyldig anmodning. Prøv venligst igen.');
    redirect('/registrer');
}

$name = sanitize($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirm'] ?? '';

// Validate fields
$errors = [];
if (empty($name)) $errors[] = 'Navn er påkrævet.';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'En gyldig e-mailadresse er påkrævet.';
if (empty($password) || strlen($password) < 8) $errors[] = 'Adgangskoden skal være mindst 8 tegn.';
if ($password !== $passwordConfirm) $errors[] = 'Adgangskoderne stemmer ikke overens.';

if (!empty($errors)) {
    flashMessage('error', implode(' ', $errors));
    redirect('/registrer');
}

// Check if email already exists for this tenant
if (User::emailExistsForTenant($email, $tenantId)) {
    flashMessage('error', 'Denne e-mailadresse er allerede registreret.');
    redirect('/registrer');
}

// Create user with customer role
$userId = User::create([
    'tenant_id' => $tenantId,
    'role' => 'customer',
    'name' => $name,
    'email' => $email,
    'password' => $password,
    'status' => 'active',
]);

// Log them in
$user = User::find($userId);
Auth::login($user);

logAudit('customer_registered', 'user', $userId);

flashMessage('success', 'Velkommen! Din konto er oprettet.');
redirect('/konto');
