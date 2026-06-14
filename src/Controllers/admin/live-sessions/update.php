<?php

use App\Models\LiveSession;

if (!isPost()) redirect('/admin/live-sessions');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/live-sessions');
}

$id = (int)($_POST['id'] ?? 0);
$tenantId = currentTenantId();

if (!$id) redirect('/admin/live-sessions');

$session = LiveSession::find($id, $tenantId);
if (!$session) {
    flashMessage('error', 'Live session not found.');
    redirect('/admin/live-sessions');
}

$title = sanitize($_POST['title'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$minTierLevel = (int)($_POST['min_tier_level'] ?? 0);
$scheduledAt = sanitize($_POST['scheduled_at'] ?? '');
$durationMinutes = (int)($_POST['duration_minutes'] ?? 60);
$meetingUrl = sanitize($_POST['meeting_url'] ?? '');
$status = sanitize($_POST['status'] ?? 'scheduled');

if (!$title) {
    flashMessage('error', 'Title is required.');
    redirect('/admin/live-sessions/edit?id=' . $id);
}

if (!$scheduledAt) {
    flashMessage('error', 'Scheduled date/time is required.');
    redirect('/admin/live-sessions/edit?id=' . $id);
}

LiveSession::update($id, [
    'title' => $title,
    'description' => $description ?: null,
    'min_tier_level' => $minTierLevel,
    'scheduled_at' => $scheduledAt,
    'duration_minutes' => $durationMinutes,
    'meeting_url' => $meetingUrl ?: null,
    'status' => $status,
]);

logAudit('live_session_updated', 'live_session', $id);
flashMessage('success', 'Live session updated successfully.');
redirect('/admin/live-sessions');
