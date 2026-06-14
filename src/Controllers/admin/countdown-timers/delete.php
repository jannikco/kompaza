<?php

use App\Auth\Auth;
use App\Models\CountdownTimer;

Auth::requireTenantAdmin();

$tenantId = currentTenantId();

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/admin/countdown-timers');
}

$id = (int)($_POST['id'] ?? 0);
CountdownTimer::delete($id, $tenantId);

flashMessage('success', 'Timer deleted.');
redirect('/admin/countdown-timers');
