<?php

use App\Auth\Auth;
use App\Models\Certificate;
use App\Models\Course;
use App\Services\CertificateService;

Auth::requireCustomer();

$tenant = currentTenant();
$tenantId = currentTenantId();
$userId = currentUserId();
$certId = (int)($_GET['id'] ?? 0);

$certificate = Certificate::find($certId, $tenantId);
if (!$certificate || $certificate['user_id'] != $userId) {
    flashMessage('error', 'Certificate not found.');
    redirect('/konto/certificates');
}

if ($certificate['revoked_at']) {
    flashMessage('error', 'This certificate has been revoked.');
    redirect('/konto/certificates');
}

$user = currentUser();
$course = Course::find($certificate['course_id']);

$html = CertificateService::generateHtml($certificate, $user, $course, $tenant);

header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: inline; filename="certificate-' . $certificate['certificate_number'] . '.html"');
echo $html;
exit;
