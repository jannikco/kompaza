<?php

use App\Models\Certificate;

$tenantId = currentTenantId();

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/admin/certificates');
}

$certId = (int)($_POST['id'] ?? 0);
$reason = sanitize($_POST['reason'] ?? '');

$certificate = Certificate::find($certId, $tenantId);
if (!$certificate) {
    flashMessage('error', 'Certificate not found.');
    redirect('/admin/certificates');
}

Certificate::revoke($certId, $tenantId, $reason ?: null);

flashMessage('success', 'Certificate revoked.');
redirect('/admin/certificates');
