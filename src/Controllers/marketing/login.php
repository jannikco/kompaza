<?php

// Redirect if already authenticated
if (isAuthenticated()) {
    if (isSuperAdmin()) {
        redirect('/');
    } elseif (isTenantAdmin()) {
        redirect('/admin');
    }
}

$error = null;
$old = [];

// Restore error from cookie if available (after POST redirect)
if (isset($_COOKIE['kz_login_error'])) {
    $error = $_COOKIE['kz_login_error'];
    setcookie('kz_login_error', '', time() - 3600, '/', '', true, true);
}

// Restore old form data from cookie
if (isset($_COOKIE['kz_login_old'])) {
    $old = json_decode($_COOKIE['kz_login_old'], true) ?: [];
    setcookie('kz_login_old', '', time() - 3600, '/', '', true, true);
}

$pageTitle = 'Log In - Kompaza';
$metaDescription = 'Log in to your Kompaza workspace. Access your content marketing dashboard, customer management, and LinkedIn automation tools.';

ob_start();
include VIEWS_PATH . '/marketing/login.php';
$content = ob_get_clean();

include VIEWS_PATH . '/marketing/layout.php';
