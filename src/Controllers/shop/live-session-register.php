<?php

use App\Auth\Auth;
use App\Models\LiveSession;
use App\Models\LiveSessionRegistration;
use App\Services\MembershipGuard;

Auth::requireCustomer();

if (!isPost()) redirect('/live-qa');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request. Please try again.');
    redirect('/live-qa');
}

if (!checkRateLimit(getClientIp(), 'live_session_register', 10, 3600)) {
    flashMessage('error', 'Too many submissions. Please try again later.');
    redirect('/live-qa');
}

$tenantId = currentTenantId();
$userId = currentUserId();

$sessionId = (int)($_POST['session_id'] ?? 0);
$session = LiveSession::find($sessionId, $tenantId);

if (!$session) {
    flashMessage('error', 'Live session not found.');
    redirect('/live-qa');
}

if (!in_array($session['status'], ['scheduled', 'live'])) {
    flashMessage('error', 'Registration is not open for this session.');
    redirect('/live-qa');
}

// Check tier access
$userTierLevel = MembershipGuard::getTierLevel($userId, $tenantId);
if ($userTierLevel < (int)$session['min_tier_level']) {
    flashMessage('error', 'You need to upgrade your membership to register for this session.');
    redirect('/live-qa');
}

// Check if already registered
if (LiveSessionRegistration::isRegistered($sessionId, $userId)) {
    flashMessage('info', 'You are already registered for this session.');
    redirect('/live-qa');
}

LiveSessionRegistration::create([
    'tenant_id' => $tenantId,
    'session_id' => $sessionId,
    'user_id' => $userId,
]);

logAudit('live_session_registered', 'live_session', $sessionId);
flashMessage('success', 'You are registered! You will receive details before the session.');
redirect('/live-qa');
