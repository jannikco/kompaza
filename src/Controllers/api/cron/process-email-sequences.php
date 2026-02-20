<?php
/**
 * Email Sequence Cron Processor
 * Run via cron every 5-15 minutes:
 * 0/5 * * * * curl -s https://yourdomain.com/api/cron/process-email-sequences?key=CRON_SECRET
 *
 * Processes due email sends for all active sequences across all tenants.
 */

use App\Models\EmailSequence;
use App\Services\EmailServiceFactory;

// Verify cron secret to prevent unauthorized access
$cronSecret = $_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '';
if (empty($cronSecret) || $cronSecret !== (defined('CRON_SECRET') ? CRON_SECRET : '')) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$results = [
    'processed' => 0,
    'sent' => 0,
    'failed' => 0,
    'errors' => [],
    'started_at' => date('Y-m-d H:i:s'),
];

try {
    $dueEmails = EmailSequence::getDueEmails();
    $results['processed'] = count($dueEmails);

    // Cache tenants to avoid repeated lookups
    $tenantCache = [];
    $emailServiceCache = [];
    $db = \App\Database\Database::getConnection();

    foreach ($dueEmails as $due) {
        $tenantId = $due['tenant_id'];

        // Look up tenant (with cache)
        if (!isset($tenantCache[$tenantId])) {
            $stmt = $db->prepare("SELECT * FROM tenants WHERE id = ?");
            $stmt->execute([$tenantId]);
            $tenantCache[$tenantId] = $stmt->fetch();
        }
        $tenant = $tenantCache[$tenantId];

        if (!$tenant) {
            EmailSequence::logSend($due['enrollment_id'], $due['step_id'], 'failed', 'Tenant not found');
            $results['failed']++;
            $results['errors'][] = "Tenant {$tenantId} not found for enrollment {$due['enrollment_id']}";
            continue;
        }

        // Create email service for this tenant (with cache)
        if (!isset($emailServiceCache[$tenantId])) {
            try {
                $emailServiceCache[$tenantId] = EmailServiceFactory::create($tenant);
            } catch (\Exception $e) {
                $emailServiceCache[$tenantId] = null;
                $results['errors'][] = "Failed to create email service for tenant {$tenantId}: " . $e->getMessage();
            }
        }
        $emailService = $emailServiceCache[$tenantId];

        if (!$emailService || !$emailService->isConfigured()) {
            EmailSequence::logSend($due['enrollment_id'], $due['step_id'], 'failed', 'Email service not configured');
            $results['failed']++;
            continue;
        }

        // Determine sender info
        $senderEmail = $tenant['sender_email'] ?? $tenant['email'] ?? ('noreply@' . ($tenant['custom_domain'] ?? 'kompaza.com'));
        $senderName = $tenant['sender_name'] ?? $tenant['company_name'] ?? $tenant['name'] ?? 'Kompaza';

        try {
            $emailService->sendTransactionalEmail(
                ['email' => $due['email'], 'name' => $due['name'] ?? ''],
                $due['subject'],
                $due['body_html'] ?? '',
                $senderEmail,
                $senderName
            );

            EmailSequence::logSend($due['enrollment_id'], $due['step_id'], 'sent');
            EmailSequence::advanceEnrollment($due['enrollment_id'], $due['sequence_id']);
            $results['sent']++;
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            EmailSequence::logSend($due['enrollment_id'], $due['step_id'], 'failed', $errorMsg);
            $results['failed']++;
            $results['errors'][] = "Failed to send to {$due['email']} (enrollment {$due['enrollment_id']}): {$errorMsg}";
            error_log("Email sequence send error: enrollment={$due['enrollment_id']}, step={$due['step_id']}: {$errorMsg}");
        }
    }
} catch (\Exception $e) {
    $results['errors'][] = 'Fatal error: ' . $e->getMessage();
    error_log('Email sequence cron fatal error: ' . $e->getMessage());
}

$results['finished_at'] = date('Y-m-d H:i:s');

echo json_encode($results, JSON_PRETTY_PRINT);
