<?php

use App\Auth\Auth;
use App\Models\Admin;

$uid = (int) ($_GET['uid'] ?? 0);
$ts = (int) ($_GET['ts'] ?? 0);
$sig = $_GET['sig'] ?? '';

// Validate required params
if (!$uid || !$ts || !$sig) {
    flashMessage('error', 'Ugyldigt impersonation-link.');
    redirect('/login');
}

// Validate timestamp (60 second window)
if (abs(time() - $ts) > 60) {
    flashMessage('error', 'Impersonation-linket er udløbet. Prøv igen.');
    redirect('/login');
}

// Validate HMAC signature
$expectedSig = hash_hmac('sha256', $uid . '|' . $ts, APP_SECRET);
if (!hash_equals($expectedSig, $sig)) {
    flashMessage('error', 'Ugyldig signatur på impersonation-link.');
    redirect('/login');
}

// Find the user
$admin = Admin::find($uid);
if (!$admin) {
    flashMessage('error', 'Bruger ikke fundet.');
    redirect('/login');
}

// Log in as the target user
Auth::login($admin);

// Set impersonating cookie (non-HttpOnly so JS can read it for banner)
setcookie('impersonating', '1', time() + 86400, '/', '', true, false);

// Redirect to admin dashboard
redirect('/admin');
