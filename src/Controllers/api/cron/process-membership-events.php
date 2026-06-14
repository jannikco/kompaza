<?php

use App\Models\MembershipPlan;
use App\Services\EmailServiceFactory;
use App\Database\Database;

header('Content-Type: application/json');

// Verify cron secret to prevent unauthorized access (fails closed if CRON_SECRET is unset)
$cronKey = $_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '';
$cronSecret = defined('CRON_SECRET') ? CRON_SECRET : '';
if ($cronSecret === '' || !hash_equals($cronSecret, $cronKey)) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// event_type (as logged by MembershipEvent::log) -> email template + subject
$templates = [
    'membership_started'   => ['view' => 'emails/membership-welcome',        'subject' => 'Welcome to your membership'],
    'membership_updated'   => ['view' => 'emails/membership-upgraded',       'subject' => 'Your membership has been upgraded'],
    'membership_cancelled' => ['view' => 'emails/membership-cancelled',      'subject' => 'Your membership has been cancelled'],
    'payment_failed'       => ['view' => 'emails/membership-payment-failed', 'subject' => 'Payment failed for your membership'],
];

$db = Database::getConnection();

// Pull events that still need a lifecycle email. Join the user + tenant up front so
// one query covers the loop; only event types we have a template for are eligible.
$stmt = $db->prepare("
    SELECT me.id, me.tenant_id, me.user_id, me.event_type,
           u.name AS user_name, u.email AS user_email
    FROM membership_events me
    JOIN users u ON me.user_id = u.id
    WHERE me.email_sent = 0
      AND me.event_type IN ('membership_started','membership_updated','membership_cancelled','payment_failed')
    ORDER BY me.id ASC
    LIMIT 200
");
$stmt->execute();
$events = $stmt->fetchAll();

$markSent = $db->prepare("UPDATE membership_events SET email_sent = 1, email_sent_at = NOW() WHERE id = ?");

// Cache tenant rows so EmailServiceFactory + tenantUrl have the full tenant config
$tenantCache = [];

$processed = 0;
$skipped = 0;
$errors = 0;

foreach ($events as $event) {
    try {
        $template = $templates[$event['event_type']] ?? null;
        $userEmail = $event['user_email'] ?? '';
        if (!$template || $userEmail === '') {
            // Nothing we can send; mark handled so it doesn't loop forever
            $markSent->execute([$event['id']]);
            $skipped++;
            continue;
        }

        $tenantId = $event['tenant_id'];
        if (!array_key_exists($tenantId, $tenantCache)) {
            $tstmt = $db->prepare("SELECT * FROM tenants WHERE id = ?");
            $tstmt->execute([$tenantId]);
            $tenantCache[$tenantId] = $tstmt->fetch() ?: null;
        }
        $tenant = $tenantCache[$tenantId];
        if (!$tenant) {
            $markSent->execute([$event['id']]);
            $skipped++;
            continue;
        }

        $emailService = EmailServiceFactory::create($tenant);
        if (!$emailService->isConfigured()) {
            // Tenant has no working email config; skip without marking so it retries
            // once they configure email.
            $skipped++;
            continue;
        }

        // Resolve the member's current plan name (best-effort; empty if unknown)
        $membership = \App\Models\CustomerMembership::findByUser($event['user_id'], $tenantId);
        $planName = $membership['plan_name'] ?? '';
        if ($planName === '' && !empty($membership['plan_id'])) {
            $plan = MembershipPlan::find($membership['plan_id']);
            $planName = $plan['name'] ?? '';
        }

        // Variables the templates expect (membership-welcome / -upgraded use
        // $dashboardUrl, -cancelled uses $resubscribeUrl, -payment-failed uses $portalUrl).
        // The cron has no request host, so build absolute per-tenant URLs from the tenant row.
        $userName      = $event['user_name'] ?? '';
        $dashboardUrl  = tenantUrl('membership/dashboard', $tenant);
        $resubscribeUrl = tenantUrl('membership', $tenant);
        $portalUrl     = tenantUrl('membership/portal', $tenant);

        // Render the email body
        ob_start();
        include VIEWS_PATH . '/' . $template['view'] . '.php';
        $html = ob_get_clean();

        $emailService->sendTransactionalEmail($userEmail, $template['subject'], $html);
        $markSent->execute([$event['id']]);
        $processed++;

    } catch (Exception $e) {
        $errors++;
        if (defined('APP_DEBUG') && APP_DEBUG) {
            error_log("Membership event email failed for event #{$event['id']}: " . $e->getMessage());
        }
    }
}

echo json_encode([
    'success' => true,
    'processed' => $processed,
    'skipped' => $skipped,
    'errors' => $errors,
]);
