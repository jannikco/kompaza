<?php

use App\Models\Course;
use App\Models\EmailSignup;

if (!isPost()) redirect('/kurser');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/kurser');
}

$tenantId = currentTenantId();
$courseId = (int)($_POST['course_id'] ?? 0);
$email = sanitize($_POST['email'] ?? '');
$name = sanitize($_POST['name'] ?? '');

$course = Course::find($courseId, $tenantId);
if (!$course) {
    flashMessage('error', 'Course not found.');
    redirect('/kurser');
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flashMessage('error', 'Please enter a valid email address.');
    redirect('/course/' . $course['slug']);
}

// Check rate limit
if (!checkRateLimit('waitlist_' . md5($_SERVER['REMOTE_ADDR'] ?? ''), 10, 3600)) {
    flashMessage('error', 'Too many requests. Please try again later.');
    redirect('/course/' . $course['slug']);
}

// Check if already on waitlist for this course
$existing = EmailSignup::findByEmailAndSource($email, $tenantId, 'waitlist', $courseId);
if ($existing) {
    flashMessage('info', 'You are already on the waitlist for this course!');
    redirect('/course/' . $course['slug']);
}

EmailSignup::create([
    'tenant_id' => $tenantId,
    'email' => $email,
    'name' => $name ?: null,
    'source_type' => 'waitlist',
    'source_id' => $courseId,
    'source_slug' => $course['slug'],
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
]);

logAudit('waitlist_signup', 'course', $courseId, ['email' => $email]);
flashMessage('success', 'You have been added to the waitlist! We will notify you when the course launches.');
redirect('/course/' . $course['slug']);
