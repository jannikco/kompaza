<?php

use App\Models\Webinar;
use App\Models\WebinarRegistration;
use App\Models\EmailSequence;

$tenant = currentTenant();
$tenantId = currentTenantId();

$webinarId = (int)($_POST['webinar_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');

if (!$webinarId || !$name || !$email) {
    flashMessage('error', 'Name and email are required.');
    redirect($_SERVER['HTTP_REFERER'] ?? '/');
}

$webinar = Webinar::find($webinarId, $tenantId);
if (!$webinar || !in_array($webinar['status'], ['registration_open', 'draft'])) {
    flashMessage('error', 'Registration is not open for this webinar.');
    redirect('/');
}

// Check if already registered
$existing = WebinarRegistration::findByEmail($webinarId, $email);
if ($existing) {
    $_SESSION['webinar_registered_' . $webinarId] = true;
    flashMessage('success', 'You are already registered for this webinar!');
    redirect('/webinar/' . $webinar['slug']);
}

// Register
$userId = currentUser() ? currentUser()['id'] : null;
WebinarRegistration::create([
    'webinar_id' => $webinarId,
    'user_id' => $userId,
    'name' => $name,
    'email' => $email,
    'phone' => trim($_POST['phone'] ?? '') ?: null,
]);

Webinar::incrementRegistrations($webinarId);
$_SESSION['webinar_registered_' . $webinarId] = true;

// Enroll in reminder sequence if configured
if ($webinar['reminder_sequence_id']) {
    EmailSequence::enrollUser($webinar['reminder_sequence_id'], $email, $name, $userId);
}

flashMessage('success', 'You are registered! Check your email for details.');
redirect('/webinar/' . $webinar['slug']);
