<?php

use App\Models\LinkedInAccount;
use App\Services\Crypto\SecretBox;
use App\Services\LinkedInService;

if (!isPost()) redirect('/admin/connectpilot/konto');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/connectpilot/konto');
}

$tenantId = currentTenantId();
$liAtCookie = trim($_POST['li_at_cookie'] ?? '');
$csrfToken = trim($_POST['csrf_token'] ?? '');
$dailyConnectionLimit = (int)($_POST['daily_connection_limit'] ?? 20);
$dailyMessageLimit = (int)($_POST['daily_message_limit'] ?? 50);
$emergencyStop = isset($_POST['emergency_stop']) ? 1 : 0;

if (empty($liAtCookie) || empty($csrfToken)) {
    flashMessage('error', 'Both li_at cookie and CSRF token are required.');
    redirect('/admin/connectpilot/konto');
}

// Clamp limits. 0 on the DM cap is the emergency stop (Leadshark-compatible).
$dailyConnectionLimit = max(0, min(100, $dailyConnectionLimit));
$dailyMessageLimit = max(0, min(200, $dailyMessageLimit));
if ($dailyMessageLimit === 0) {
    $emergencyStop = 1;
}

// Validate the cookie by calling LinkedIn API
$linkedin = new LinkedInService($liAtCookie, $csrfToken);
$profile = $linkedin->validateCookie();

$db = \App\Database\Database::getConnection();

// Check if account already exists
$stmt = $db->prepare("SELECT id FROM linkedin_accounts WHERE tenant_id = ? LIMIT 1");
$stmt->execute([$tenantId]);
$existing = $stmt->fetch();

if ($profile !== false) {
    // Extract profile data
    $linkedinName = $profile['firstName'] ?? '';
    if (!empty($profile['lastName'])) {
        $linkedinName .= ' ' . $profile['lastName'];
    }
    $linkedinEmail = $profile['emailAddress'] ?? $profile['miniProfile']['publicIdentifier'] ?? '';
    $linkedinProfileUrl = '';
    if (!empty($profile['miniProfile']['publicIdentifier'])) {
        $linkedinProfileUrl = 'https://www.linkedin.com/in/' . $profile['miniProfile']['publicIdentifier'];
    } elseif (!empty($profile['publicIdentifier'])) {
        $linkedinProfileUrl = 'https://www.linkedin.com/in/' . $profile['publicIdentifier'];
    }

    // Encryption must be available before we accept a session. Failing closed here
    // is deliberate: silently storing the cookie in plaintext because a key is
    // missing would reintroduce exactly the vulnerability we removed.
    if (!SecretBox::isConfigured()) {
        flashMessage('error', 'Server encryption key is not configured, so LinkedIn sessions cannot be stored securely. Set OUTREACH_ENCRYPTION_KEY and try again.');
        redirect('/admin/connectpilot/konto');
    }

    // Capture the connected member's own identifiers. profile_urn is what
    // LinkedInService::sendMessage() addresses the sender's mailbox with; without it
    // no DM can be sent, and nothing populated it before.
    $identity = LinkedInService::extractSelfIdentity($profile);

    $profileData = [
        'linkedin_name' => $linkedinName,
        'linkedin_email' => $linkedinEmail,
        'linkedin_profile_url' => $linkedinProfileUrl,
        'daily_connection_limit' => $dailyConnectionLimit,
        'daily_message_limit' => $dailyMessageLimit,
        'emergency_stop' => $emergencyStop,
        'emergency_stopped_at' => $emergencyStop ? date('Y-m-d H:i:s') : null,
        'emergency_stopped_by' => $emergencyStop ? currentUserId() : null,
        'profile_urn' => $identity['profile_urn'],
        'member_urn' => $identity['member_urn'],
        'public_identifier' => $identity['public_identifier'],
    ];

    if ($existing) {
        $accountId = (int) $existing['id'];
    } else {
        // user_id is NOT NULL with no default — omitting it made this INSERT throw
        // (errno 1364), so connecting an account has never succeeded on this path.
        $stmt = $db->prepare("
            INSERT INTO linkedin_accounts (tenant_id, user_id, status, created_at, updated_at)
            VALUES (?, ?, 'active', NOW(), NOW())
        ");
        $stmt->execute([$tenantId, currentUserId()]);
        $accountId = (int) $db->lastInsertId();
    }

    // One path for the profile fields, so a newly connected account gets the same
    // identity columns an existing one does — profile_urn in particular, without which
    // no DM can be sent.
    LinkedInAccount::update($accountId, $profileData);

    // The cookies themselves only ever go through the model, which encrypts them.
    LinkedInAccount::storeSession($accountId, $liAtCookie, $csrfToken, null, 'manual');

    \App\Models\OutreachActivityLimit::seedForAccount($tenantId, $accountId, $profileData);
    \App\Models\OutreachActivityLimit::setLimit($accountId, 'dm', $dailyMessageLimit);
    \App\Models\OutreachActivityLimit::setLimit($accountId, 'connection_daily', $dailyConnectionLimit);

    logAudit('linkedin_account_connected', 'linkedin_account', $accountId);
    $msg = 'LinkedIn account connected successfully.';
    if ($emergencyStop) {
        $msg .= ' Daily DM cap is 0 — all sending is stopped until you raise it.';
    }
    flashMessage('success', $msg);
} else {
    // Cookie validation failed - save but mark as disconnected
    if ($existing) {
        $stmt = $db->prepare("UPDATE linkedin_accounts SET status = 'disconnected', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$existing['id']]);
    }
    flashMessage('error', 'Could not validate LinkedIn cookie. The session may be expired or invalid. Please check your credentials and try again.');
}

redirect('/admin/connectpilot/konto');
