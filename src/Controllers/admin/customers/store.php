<?php

use App\Models\User;

if (!isPost()) redirect('/admin/kunder');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/kunder/opret');
}

$tenantId = currentTenantId();

$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$name || !$email || !$password) {
    flashMessage('error', 'Navn, e-mail og adgangskode er påkrævet.');
    redirect('/admin/kunder/opret');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flashMessage('error', 'Ugyldig e-mailadresse.');
    redirect('/admin/kunder/opret');
}

if (strlen($password) < 8) {
    flashMessage('error', 'Adgangskoden skal være mindst 8 tegn.');
    redirect('/admin/kunder/opret');
}

if (User::emailExistsForTenant($email, $tenantId)) {
    flashMessage('error', 'E-mailadressen er allerede i brug.');
    redirect('/admin/kunder/opret');
}

$id = User::create([
    'tenant_id' => $tenantId,
    'role' => 'customer',
    'name' => $name,
    'email' => $email,
    'password' => $password,
    'phone' => sanitize($_POST['phone'] ?? ''),
    'company' => sanitize($_POST['company'] ?? ''),
    'address_line1' => sanitize($_POST['address_line1'] ?? ''),
    'address_line2' => sanitize($_POST['address_line2'] ?? ''),
    'postal_code' => sanitize($_POST['postal_code'] ?? ''),
    'city' => sanitize($_POST['city'] ?? ''),
    'country' => sanitize($_POST['country'] ?? 'DK'),
    'cvr_number' => sanitize($_POST['cvr_number'] ?? ''),
]);

logAudit('customer_created', 'user', $id);
flashMessage('success', 'Kunde oprettet.');
redirect('/admin/kunder');
