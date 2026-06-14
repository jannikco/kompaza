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
$timer = CountdownTimer::find($id, $tenantId);
if (!$timer) {
    flashMessage('error', 'Timer not found.');
    redirect('/admin/countdown-timers');
}

$timerType = sanitize($_POST['timer_type'] ?? 'fixed');
$endDate = null;
$durationMinutes = null;

if ($timerType === 'fixed') {
    $endDate = $_POST['end_date'] ?? null;
} else {
    $durationMinutes = (int)($_POST['duration_minutes'] ?? 60);
}

CountdownTimer::update($id, [
    'name' => sanitize($_POST['name'] ?? ''),
    'timer_type' => $timerType,
    'headline' => sanitize($_POST['headline'] ?? ''),
    'subheadline' => sanitize($_POST['subheadline'] ?? ''),
    'end_date' => $endDate,
    'duration_minutes' => $durationMinutes,
    'redirect_url' => sanitize($_POST['redirect_url'] ?? ''),
    'expired_action' => sanitize($_POST['expired_action'] ?? 'hide'),
    'expired_message' => sanitize($_POST['expired_message'] ?? ''),
    'style_preset' => sanitize($_POST['style_preset'] ?? 'default'),
    'bg_color' => sanitize($_POST['bg_color'] ?? '#111827'),
    'text_color' => sanitize($_POST['text_color'] ?? '#FFFFFF'),
    'accent_color' => sanitize($_POST['accent_color'] ?? '#EF4444'),
    'status' => sanitize($_POST['status'] ?? 'active'),
]);

flashMessage('success', 'Timer updated.');
redirect('/admin/countdown-timers');
