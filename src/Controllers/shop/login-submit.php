<?php

use App\Auth\Auth;

$tenant = currentTenant();
$tenantId = currentTenantId();

// Verify CSRF
$csrfToken = $_POST[CSRF_TOKEN_NAME] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    flashMessage('error', 'Ugyldig anmodning. Prøv venligst igen.');
    redirect('/login');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    flashMessage('error', 'Indtast venligst e-mail og adgangskode.');
    redirect('/login');
}

// Rate limit: 10 attempts per 15 minutes per IP
$ip = getClientIp();
if (!checkRateLimit($ip, 'customer_login', 10, 900)) {
    flashMessage('error', 'For mange loginforsøg. Prøv igen om lidt.');
    redirect('/login');
}

if (Auth::attempt($email, $password, $tenantId)) {
    logAudit('user_login', 'user', Auth::id());

    // Check for saved redirect
    $redirectTo = $_COOKIE['kz_redirect'] ?? null;
    setcookie('kz_redirect', '', time() - 3600, '/', '', true, true);

    // Default redirect based on role
    if (!$redirectTo) {
        $redirectTo = Auth::isTenantAdmin() ? '/admin' : '/konto';
    }

    redirect($redirectTo);
} else {
    flashMessage('error', 'Forkert e-mail eller adgangskode.');
    redirect('/login');
}
