<?php

use App\Auth\Auth;
use App\Models\CountdownTimer;

Auth::requireTenantAdmin();

$tenantId = currentTenantId();
$id = (int)($_GET['id'] ?? 0);

$timer = CountdownTimer::find($id, $tenantId);
if (!$timer) {
    flashMessage('error', 'Timer not found.');
    redirect('/admin/countdown-timers');
}

view('admin/countdown-timers/form', [
    'timer' => $timer,
]);
