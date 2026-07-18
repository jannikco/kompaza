<?php
/**
 * Workshop opt-in → enroll in track nurture sequence + unlock watch.
 * POST /workshop/submit  fields: email, name, track
 */

use App\Models\EmailSignup;
use App\Models\EmailSequence;
use App\Database\Database;

if (!isPost()) {
    redirect('/workshop');
}

$ip = getClientIp();
if (!checkRateLimit($ip, 'workshop_optin', 8, 3600)) {
    flashMessage('error', 'Too many attempts. Please try again later.');
    redirect('/workshop');
}

$email = trim($_POST['email'] ?? '');
$name = trim($_POST['name'] ?? '');
$track = preg_replace('/[^a-z0-9\-]/', '', strtolower($_POST['track'] ?? 'creator-os'));
if (!in_array($track, ['office-os', 'creator-os', 'founder-os'], true)) {
    $track = 'creator-os';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flashMessage('error', 'Please enter a valid email.');
    redirect('/' . $track . '/workshop');
}

$tenant = currentTenant();
$tenantId = currentTenantId();
$locale = (str_starts_with($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 'en') || ($_COOKIE['kz_lang'] ?? '') === 'en') ? 'en' : 'da';
if (!empty($_POST['locale']) && in_array($_POST['locale'], ['da', 'en'], true)) {
    $locale = $_POST['locale'];
}

try {
    EmailSignup::create([
        'tenant_id' => $tenantId,
        'email' => $email,
        'name' => $name ?: null,
        'source_type' => 'waitlist',
        'source_slug' => 'workshop-' . $track,
        'ip_address' => $ip,
    ]);
} catch (\Exception $e) { /* dupes ok */ }

// Enroll into matching email sequence by name
$seqName = 'Workshop nurture: ' . $track . ' (' . $locale . ')';
try {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT id FROM email_sequences WHERE tenant_id=? AND name=? AND status='active' LIMIT 1");
    $stmt->execute([$tenantId, $seqName]);
    $seqId = $stmt->fetchColumn();
    if (!$seqId) {
        // fallback any workshop sequence for track
        $stmt = $db->prepare("SELECT id FROM email_sequences WHERE tenant_id=? AND name LIKE ? AND status='active' LIMIT 1");
        $stmt->execute([$tenantId, 'Workshop nurture: ' . $track . '%']);
        $seqId = $stmt->fetchColumn();
    }
    if ($seqId) {
        EmailSequence::enrollUser((int)$seqId, $email, $name ?: null, null);
    }
} catch (\Exception $e) {
    error_log('workshop enroll: ' . $e->getMessage());
}

// Cookie to unlock watch page
setcookie('kz_workshop_' . $track, '1', time() + 30 * 86400, '/', '', true, false);

flashMessage('success', 'You are in — enjoy the workshop.');
redirect('/workshop/watch?track=' . urlencode($track));
