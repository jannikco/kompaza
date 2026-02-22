<?php

$email = $_GET['email'] ?? '';

$pageTitle = 'Verify Your Email | Kompaza';
$metaDescription = 'Please check your email to verify your Kompaza account.';

ob_start();
include VIEWS_PATH . '/marketing/verify-pending.php';
$content = ob_get_clean();

include VIEWS_PATH . '/marketing/layout.php';
