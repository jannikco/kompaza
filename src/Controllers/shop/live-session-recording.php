<?php

use App\Auth\Auth;
use App\Models\LiveSession;
use App\Services\MembershipGuard;
use App\Services\S3Service;

Auth::requireCustomer();

$tenantId = currentTenantId();
$userId = currentUserId();
$sessionId = (int)($id ?? 0);

$session = LiveSession::find($sessionId, $tenantId);
if (!$session || empty($session['recording_s3_key'])) {
    http_response_code(404);
    view('errors/404');
    exit;
}

// Access control: membership tier must meet the session's required tier
$minTier = (int)($session['min_tier_level'] ?? 0);
if ($minTier > 0 && MembershipGuard::getTierLevel($userId, $tenantId) < $minTier) {
    flashMessage('error', 'You need a higher membership tier to watch this recording.');
    redirect('/live-qa');
}

// Serve the recording via a short-lived presigned URL
if (S3Service::isConfigured()) {
    $s3 = new S3Service();
    $url = $s3->getPresignedUrl($session['recording_s3_key'], 3600);
    if ($url) {
        redirect($url);
    }
}

http_response_code(404);
view('errors/404');
exit;
