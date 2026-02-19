<?php

use App\Models\User;

if (!isPost()) redirect('/admin/kunder');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/kunder');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/kunder');

$customer = User::find($id);
if (!$customer || $customer['tenant_id'] != $tenantId || $customer['role'] !== 'customer') {
    flashMessage('error', 'Kunde ikke fundet.');
    redirect('/admin/kunder');
}

$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');

if (!$name || !$email) {
    flashMessage('error', 'Navn og e-mail er påkrævet.');
    redirect('/admin/kunder/rediger?id=' . $id);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flashMessage('error', 'Ugyldig e-mailadresse.');
    redirect('/admin/kunder/rediger?id=' . $id);
}

if (User::emailExistsForTenant($email, $tenantId, $id)) {
    flashMessage('error', 'E-mailadressen er allerede i brug.');
    redirect('/admin/kunder/rediger?id=' . $id);
}

$data = [
    'name' => $name,
    'email' => $email,
    'phone' => sanitize($_POST['phone'] ?? ''),
    'company' => sanitize($_POST['company'] ?? ''),
    'address_line1' => sanitize($_POST['address_line1'] ?? ''),
    'address_line2' => sanitize($_POST['address_line2'] ?? ''),
    'postal_code' => sanitize($_POST['postal_code'] ?? ''),
    'city' => sanitize($_POST['city'] ?? ''),
    'country' => sanitize($_POST['country'] ?? 'DK'),
    'cvr_number' => sanitize($_POST['cvr_number'] ?? ''),
    'status' => sanitize($_POST['status'] ?? 'active'),
];

User::update($id, $data);

// Update password if provided
$password = $_POST['password'] ?? '';
if ($password) {
    if (strlen($password) < 8) {
        flashMessage('error', 'Adgangskoden skal være mindst 8 tegn.');
        redirect('/admin/kunder/rediger?id=' . $id);
    }
    User::updatePassword($id, $password);
}

logAudit('customer_updated', 'user', $id);
flashMessage('success', 'Kunde opdateret.');
redirect('/admin/kunder');
