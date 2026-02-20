#!/usr/bin/env php
<?php
/**
 * ConnectPilot Sequence Runner
 * Run via cron every 5 minutes:
 * 0/5 * * * * php /var/www/kompaza.com/cron/connectpilot-runner.php
 *
 * Processes pending sequence steps for active campaigns.
 * Respects daily limits per LinkedIn account.
 */

require_once __DIR__ . '/../src/Config/config.php';

use App\Database\Database;
use App\Services\LinkedInService;

// Reset daily counters at midnight
$db = Database::getConnection();
$stmt = $db->prepare("
    UPDATE linkedin_accounts
    SET connections_sent_today = 0, messages_sent_today = 0
    WHERE DATE(last_activity_at) < CURDATE()
");
$stmt->execute();

// Get active campaigns with active LinkedIn accounts
$stmt = $db->prepare("
    SELECT c.*, la.li_at_cookie, la.csrf_token, la.daily_connection_limit, la.daily_message_limit,
           la.connections_sent_today, la.messages_sent_today, la.id as account_id
    FROM connectpilot_campaigns c
    JOIN linkedin_accounts la ON la.id = c.linkedin_account_id
    WHERE c.status = 'active'
    AND la.status = 'active'
    AND la.li_at_cookie IS NOT NULL
");
$stmt->execute();
$campaigns = $stmt->fetchAll();

foreach ($campaigns as $campaign) {
    // Get leads that need processing
    $stmt = $db->prepare("
        SELECT ll.*, lss.id as step_id, lss.action_type, lss.message_template, lss.condition_type
        FROM linkedin_leads ll
        JOIN connectpilot_sequence_steps lss ON lss.campaign_id = ll.campaign_id AND lss.step_number = ll.current_step + 1
        WHERE ll.campaign_id = ?
        AND ll.connection_status != 'rejected'
        AND (ll.last_contacted_at IS NULL OR ll.last_contacted_at < DATE_SUB(NOW(), INTERVAL lss.delay_days DAY))
    ");
    $stmt->execute([$campaign['id']]);
    $leads = $stmt->fetchAll();

    if (empty($leads)) continue;

    $linkedin = new LinkedInService($campaign['li_at_cookie'], $campaign['csrf_token']);
    $connectionsSent = $campaign['connections_sent_today'];
    $messagesSent = $campaign['messages_sent_today'];

    foreach ($leads as $lead) {
        // Check daily limits
        if ($lead['action_type'] === 'connect' && $connectionsSent >= $campaign['daily_connection_limit']) {
            break;
        }
        if (in_array($lead['action_type'], ['message', 'follow_up']) && $messagesSent >= $campaign['daily_message_limit']) {
            continue;
        }

        // Check conditions
        if ($lead['condition_type'] === 'if_accepted' && $lead['connection_status'] !== 'accepted') continue;
        if ($lead['condition_type'] === 'if_no_reply' && $lead['last_replied_at'] !== null) continue;
        if ($lead['condition_type'] === 'if_replied' && $lead['last_replied_at'] === null) continue;

        // Personalize message template
        $message = str_replace(
            ['{{first_name}}', '{{last_name}}', '{{company}}', '{{job_title}}', '{{headline}}'],
            [$lead['first_name'] ?? '', $lead['last_name'] ?? '', $lead['company'] ?? '', $lead['job_title'] ?? '', $lead['headline'] ?? ''],
            $lead['message_template'] ?? ''
        );

        $success = false;
        $errorMessage = null;

        try {
            switch ($lead['action_type']) {
                case 'connect':
                    $profileUrn = $lead['linkedin_id'] ?? '';
                    $success = $linkedin->sendConnectionRequest($profileUrn, $message);
                    if ($success) {
                        $connectionsSent++;
                        $stmt2 = $db->prepare("UPDATE linkedin_leads SET connection_status = 'pending', last_contacted_at = NOW(), current_step = current_step + 1 WHERE id = ?");
                        $stmt2->execute([$lead['id']]);
                        $stmt2 = $db->prepare("UPDATE connectpilot_campaigns SET connections_sent = connections_sent + 1 WHERE id = ?");
                        $stmt2->execute([$campaign['id']]);
                    }
                    break;

                case 'message':
                case 'follow_up':
                    $profileUrn = $lead['linkedin_id'] ?? '';
                    $success = $linkedin->sendMessage($profileUrn, $message);
                    if ($success) {
                        $messagesSent++;
                        $stmt2 = $db->prepare("UPDATE linkedin_leads SET last_contacted_at = NOW(), current_step = current_step + 1 WHERE id = ?");
                        $stmt2->execute([$lead['id']]);
                        $stmt2 = $db->prepare("UPDATE connectpilot_campaigns SET messages_sent = messages_sent + 1 WHERE id = ?");
                        $stmt2->execute([$campaign['id']]);
                    }
                    break;

                case 'view_profile':
                    $success = $linkedin->viewProfile($lead['linkedin_profile_url']);
                    if ($success) {
                        $stmt2 = $db->prepare("UPDATE linkedin_leads SET current_step = current_step + 1 WHERE id = ?");
                        $stmt2->execute([$lead['id']]);
                    }
                    break;
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            error_log("ConnectPilot error for lead {$lead['id']}: {$errorMessage}");
        }

        // Log activity
        $stmt2 = $db->prepare("
            INSERT INTO connectpilot_activity_log (tenant_id, campaign_id, lead_id, action_type, step_id, message_sent, status, error_message)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt2->execute([
            $campaign['tenant_id'],
            $campaign['id'],
            $lead['id'],
            $lead['action_type'],
            $lead['step_id'],
            $message,
            $success ? 'success' : 'failed',
            $errorMessage
        ]);

        // Random delay between actions (2-8 seconds)
        sleep(rand(2, 8));
    }

    // Update daily counters
    $stmt = $db->prepare("UPDATE linkedin_accounts SET connections_sent_today = ?, messages_sent_today = ?, last_activity_at = NOW() WHERE id = ?");
    $stmt->execute([$connectionsSent, $messagesSent, $campaign['account_id']]);
}

echo "ConnectPilot runner completed at " . date('Y-m-d H:i:s') . "\n";
