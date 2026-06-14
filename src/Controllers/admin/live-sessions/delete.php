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

LiveSession::delete($id, $tenantId);

logAudit('live_session_deleted', 'live_session', $id);
flashMessage('success', 'Live session deleted successfully.');
redirect('/admin/live-sessions');
