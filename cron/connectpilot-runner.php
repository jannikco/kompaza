#!/usr/bin/env php
<?php
/**
 * ConnectPilot Sequence Runner
 * Run via cron every 5 minutes:
 * */5 * * * * php /var/www/kompaza.com/cron/connectpilot-runner.php
 *
 * Processes pending sequence steps for active campaigns.
 * Writes go through ActionExecutor (shared budget with post automations).
 */

require_once __DIR__ . '/../src/Config/config.php';

use App\Database\Database;
use App\Services\CronLock;
use App\Services\Outreach\ActionExecutor;
use App\Services\Outreach\ActionRequest;
use App\Services\Outreach\TemplateRenderer;

$lock = CronLock::acquire('connectpilot-runner');
if (!$lock) {
    echo "Sequence runner is already running; exiting.\n";
    exit(0);
}

$db = Database::getConnection();
$stmt = $db->prepare("
    UPDATE linkedin_accounts
    SET connections_sent_today = 0, messages_sent_today = 0
    WHERE DATE(last_activity_at) < CURDATE()
");
$stmt->execute();

$stmt = $db->prepare("
    SELECT c.*, la.daily_connection_limit, la.daily_message_limit,
           la.connections_sent_today, la.messages_sent_today,
           la.profile_urn, la.emergency_stop, la.id as account_id
    FROM connectpilot_campaigns c
    JOIN linkedin_accounts la ON la.id = c.linkedin_account_id
    WHERE c.status = 'active'
    AND la.status = 'active'
    AND (la.encryption_version >= 1 OR la.li_at_cookie IS NOT NULL)
");
$stmt->execute();
$campaigns = $stmt->fetchAll();

$executor = new ActionExecutor();

foreach ($campaigns as $campaign) {
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

    if (empty($leads)) {
        continue;
    }

    foreach ($leads as $lead) {
        if ($lead['condition_type'] === 'if_accepted' && $lead['connection_status'] !== 'accepted') {
            continue;
        }
        if ($lead['condition_type'] === 'if_no_reply' && $lead['last_replied_at'] !== null) {
            continue;
        }
        if ($lead['condition_type'] === 'if_replied' && $lead['last_replied_at'] === null) {
            continue;
        }

        $message = TemplateRenderer::render((string) ($lead['message_template'] ?? ''), [
            'first_name' => $lead['first_name'] ?? '',
            'last_name'  => $lead['last_name'] ?? '',
            'full_name'  => trim(($lead['first_name'] ?? '') . ' ' . ($lead['last_name'] ?? '')),
        ]);
        $message = str_replace(
            ['{{company}}', '{{job_title}}', '{{headline}}'],
            [$lead['company'] ?? '', $lead['job_title'] ?? '', $lead['headline'] ?? ''],
            $message
        );

        $actionType = $lead['action_type'];
        $channel = match ($actionType) {
            'connect'      => 'connection_daily',
            'message', 'follow_up' => 'dm',
            'like_post'    => 'reaction',
            'view_profile' => 'profile_view',
            default        => 'none',
        };

        $result = $executor->execute(new ActionRequest(
            tenantId: (int) $campaign['tenant_id'],
            accountId: (int) $campaign['account_id'],
            actionType: $actionType === 'like_post' ? 'react' : $actionType,
            channel: $channel,
            subjectUrn: $lead['linkedin_id'] ?? $lead['member_urn'] ?? null,
            subjectLabel: trim(($lead['first_name'] ?? '') . ' ' . ($lead['last_name'] ?? '')),
            targetUrl: $lead['linkedin_profile_url'] ?? null,
            bodyText: $message,
            source: 'cron',
            sourceRef: 'campaign:' . $campaign['id'],
            toolName: 'sequence_' . $actionType,
            leadId: (int) $lead['id'],
        ));

        $success = $result->ok();
        $errorMessage = $result->errorMessage ?: $result->refusalCode;

        if ($success) {
            if ($actionType === 'connect') {
                $db->prepare("UPDATE linkedin_leads SET connection_status = 'pending', last_contacted_at = NOW(), current_step = current_step + 1 WHERE id = ?")
                    ->execute([$lead['id']]);
                $db->prepare("UPDATE connectpilot_campaigns SET connections_sent = connections_sent + 1 WHERE id = ?")
                    ->execute([$campaign['id']]);
            } elseif (in_array($actionType, ['message', 'follow_up'], true)) {
                $db->prepare("UPDATE linkedin_leads SET last_contacted_at = NOW(), current_step = current_step + 1 WHERE id = ?")
                    ->execute([$lead['id']]);
                $db->prepare("UPDATE connectpilot_campaigns SET messages_sent = messages_sent + 1 WHERE id = ?")
                    ->execute([$campaign['id']]);
            } else {
                $db->prepare("UPDATE linkedin_leads SET current_step = current_step + 1 WHERE id = ?")
                    ->execute([$lead['id']]);
            }
        }

        if ($result->refusalCode === 'limit_reached' || $result->refusalCode === 'emergency_stop') {
            echo "  Campaign {$campaign['id']}: {$result->refusalCode}, stopping this campaign for now.\n";
            $db->prepare("
                INSERT INTO connectpilot_activity_log (tenant_id, campaign_id, lead_id, action_type, step_id, message_sent, status, error_message)
                VALUES (?, ?, ?, ?, ?, ?, 'skipped', ?)
            ")->execute([
                $campaign['tenant_id'], $campaign['id'], $lead['id'],
                $actionType, $lead['step_id'], $message, $result->refusalCode,
            ]);
            break;
        }

        $db->prepare("
            INSERT INTO connectpilot_activity_log (tenant_id, campaign_id, lead_id, action_type, step_id, message_sent, status, error_message)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $campaign['tenant_id'],
            $campaign['id'],
            $lead['id'],
            $actionType,
            $lead['step_id'],
            $message,
            $success ? 'success' : 'failed',
            $errorMessage,
        ]);
    }
}

echo "ConnectPilot runner completed at " . date('Y-m-d H:i:s') . "\n";
