<?php

// Redirect if already authenticated
if (isAuthenticated()) {
    if (isSuperAdmin()) {
        redirect('/');
    } elseif (isTenantAdmin()) {
        redirect('/admin');
    }
}

$errors = [];
$old = [];
$selectedPlan = $_GET['plan'] ?? null;

// Restore old form data from cookie if available (after validation error redirect)
if (isset($_COOKIE['kz_register_old'])) {
    $old = json_decode($_COOKIE['kz_register_old'], true) ?: [];
    setcookie('kz_register_old', '', time() - 3600, '/', '', true, true);
}

// Restore errors from cookie if available
if (isset($_COOKIE['kz_register_errors'])) {
    $errors = json_decode($_COOKIE['kz_register_errors'], true) ?: [];
    setcookie('kz_register_errors', '', time() - 3600, '/', '', true, true);
}

$pageTitle = 'Sign Up - Start Your Free Trial | Kompaza';
$metaDescription = 'Create your Kompaza account and get instant access to content marketing, lead generation, and LinkedIn automation tools. 7-day free trial.';

ob_start();
include VIEWS_PATH . '/marketing/register.php';
$content = ob_get_clean();

include VIEWS_PATH . '/marketing/layout.php';
