<?php

use App\Services\LinkedInService;

if (!isPost()) redirect('/admin/leadshark/konto');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/leadshark/konto');
}

$tenantId = currentTenantId();
$liAtCookie = trim($_POST['li_at_cookie'] ?? '');
$csrfToken = trim($_POST['csrf_token'] ?? '');
$dailyConnectionLimit = (int)($_POST['daily_connection_limit'] ?? 20);
$dailyMessageLimit = (int)($_POST['daily_message_limit'] ?? 50);

if (empty($liAtCookie) || empty($csrfToken)) {
    flashMessage('error', 'Both li_at cookie and CSRF token are required.');
    redirect('/admin/leadshark/konto');
}

// Clamp limits to reasonable values
$dailyConnectionLimit = max(1, min(100, $dailyConnectionLimit));
$dailyMessageLimit = max(1, min(200, $dailyMessageLimit));

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

    $data = [
        'li_at_cookie' => $liAtCookie,
        'csrf_token' => $csrfToken,
        'linkedin_name' => $linkedinName,
        'linkedin_email' => $linkedinEmail,
        'linkedin_profile_url' => $linkedinProfileUrl,
        'status' => 'active',
        'daily_connection_limit' => $dailyConnectionLimit,
        'daily_message_limit' => $dailyMessageLimit,
    ];

    if ($existing) {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $existing['id'];
        $stmt = $db->prepare("UPDATE linkedin_accounts SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?");
        $stmt->execute($values);
    } else {
        $stmt = $db->prepare("
            INSERT INTO linkedin_accounts (tenant_id, li_at_cookie, csrf_token, linkedin_name, linkedin_email, linkedin_profile_url, status, daily_connection_limit, daily_message_limit, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, 'active', ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $tenantId,
            $liAtCookie,
            $csrfToken,
            $linkedinName,
            $linkedinEmail,
            $linkedinProfileUrl,
            $dailyConnectionLimit,
            $dailyMessageLimit,
        ]);
    }

    logAudit('linkedin_account_connected', 'linkedin_account', $existing['id'] ?? $db->lastInsertId());
    flashMessage('success', 'LinkedIn account connected successfully.');
} else {
    // Cookie validation failed - save but mark as disconnected
    if ($existing) {
        $stmt = $db->prepare("UPDATE linkedin_accounts SET status = 'disconnected', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$existing['id']]);
    }
    flashMessage('error', 'Could not validate LinkedIn cookie. The session may be expired or invalid. Please check your credentials and try again.');
}

redirect('/admin/leadshark/konto');
