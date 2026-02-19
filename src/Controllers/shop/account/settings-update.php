<?php

use App\Auth\Auth;
use App\Models\User;

Auth::requireCustomer();

$tenant = currentTenant();
$tenantId = currentTenantId();
$customer = Auth::user();

// Verify CSRF
$csrfToken = $_POST[CSRF_TOKEN_NAME] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    flashMessage('error', 'Ugyldig anmodning. Prøv venligst igen.');
    redirect('/konto/indstillinger');
}

$name = sanitize($_POST['name'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$company = sanitize($_POST['company'] ?? '');
$addressLine1 = sanitize($_POST['address_line1'] ?? '');
$addressLine2 = sanitize($_POST['address_line2'] ?? '');
$postalCode = sanitize($_POST['postal_code'] ?? '');
$city = sanitize($_POST['city'] ?? '');
$cvrNumber = sanitize($_POST['cvr_number'] ?? '');

if (empty($name)) {
    flashMessage('error', 'Navn er påkrævet.');
    redirect('/konto/indstillinger');
}

// Update profile
$updateData = [
    'name' => $name,
    'phone' => $phone,
    'company' => $company,
    'address_line1' => $addressLine1,
    'address_line2' => $addressLine2,
    'postal_code' => $postalCode,
    'city' => $city,
    'cvr_number' => $cvrNumber,
];

// Handle password change if provided
$newPassword = $_POST['new_password'] ?? '';
$newPasswordConfirm = $_POST['new_password_confirm'] ?? '';

if (!empty($newPassword)) {
    if (strlen($newPassword) < 8) {
        flashMessage('error', 'Adgangskoden skal være mindst 8 tegn.');
        redirect('/konto/indstillinger');
    }
    if ($newPassword !== $newPasswordConfirm) {
        flashMessage('error', 'Adgangskoderne stemmer ikke overens.');
        redirect('/konto/indstillinger');
    }
    User::updatePassword($customer['id'], $newPassword);
}

User::update($customer['id'], $updateData);

logAudit('customer_profile_updated', 'user', $customer['id']);

flashMessage('success', 'Dine indstillinger er gemt.');
redirect('/konto/indstillinger');
