#!/usr/bin/env php
<?php
/**
 * ConnectPilot Post Automation Runner
 * Run via cron every 5 minutes:
 * 0/5 * * * * php /var/www/kompaza.com/cron/connectpilot-post-runner.php
 *
 * Polls LinkedIn posts for new comments, matches trigger keywords,
 * auto-replies to matching comments, and sends DMs with resource links.
 */

require_once __DIR__ . '/../src/Config/config.php';

use App\Database\Database;
use App\Models\PostAutomation;
use App\Models\PostComment;
use App\Services\LinkedInService;

$db = Database::getConnection();

// Reset daily message counters at midnight
$stmt = $db->prepare("
    UPDATE linkedin_accounts
    SET messages_sent_today = 0
    WHERE DATE(last_activity_at) < CURDATE()
");
$stmt->execute();

// Get all active post automations with active LinkedIn accounts
$automations = PostAutomation::activeWithAccounts();

if (empty($automations)) {
    echo "No active post automations to process.\n";
    exit;
}

foreach ($automations as $automation) {
    $automationId = $automation['id'];
    $tenantId = $automation['tenant_id'];
    $postUrn = $automation['post_urn'];

    if (empty($postUrn)) {
        error_log("PostRunner: Automation {$automationId} has no post_urn, skipping.");
        continue;
    }

    $linkedin = new LinkedInService($automation['li_at_cookie'], $automation['csrf_token']);
    $messagesSentToday = (int)$automation['messages_sent_today'];
    $dailyMessageLimit = (int)$automation['daily_message_limit'];

    // Resolve lead magnet URL if linked
    $leadMagnetUrl = '';
    if ($automation['lead_magnet_id']) {
        $stmt = $db->prepare("SELECT slug FROM lead_magnets WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$automation['lead_magnet_id'], $tenantId]);
        $lm = $stmt->fetch();
        if ($lm) {
            // Get tenant slug for URL construction
            $stmt2 = $db->prepare("SELECT slug, custom_domain FROM tenants WHERE id = ?");
            $stmt2->execute([$tenantId]);
            $tenantData = $stmt2->fetch();
            if ($tenantData && $tenantData['custom_domain']) {
                $leadMagnetUrl = 'https://' . $tenantData['custom_domain'] . '/lp/' . $lm['slug'];
            } elseif ($tenantData) {
                $leadMagnetUrl = 'https://' . $tenantData['slug'] . '.' . PLATFORM_DOMAIN . '/lp/' . $lm['slug'];
            }
        }
    }

    echo "Processing automation {$automationId}: {$automation['name']}\n";

    // ---------------------------------------------------------------
    // Step 1: Fetch recent comments from the LinkedIn post
    // ---------------------------------------------------------------
    try {
        $response = $linkedin->getPostComments($postUrn, 0, 50);
    } catch (\Exception $e) {
        error_log("PostRunner: Error fetching comments for automation {$automationId}: " . $e->getMessage());
        PostAutomation::updateLastChecked($automationId);
        continue;
    }

    if ($response === false) {
        error_log("PostRunner: Failed to fetch comments for automation {$automationId}");
        PostAutomation::updateLastChecked($automationId);
        continue;
    }

    // Parse comments from LinkedIn response
    $comments = [];
    $elements = $response['elements'] ?? $response['data']['elements'] ?? $response['included'] ?? [];

    foreach ($elements as $element) {
        // LinkedIn returns comment data in various formats
        $commentUrn = $element['entityUrn'] ?? $element['commentUrn'] ?? $element['urn'] ?? null;
        if (!$commentUrn) continue;

        $commentText = $element['commentary'] ?? $element['comment']['values'][0]['value'] ?? $element['commentV2']['text'] ?? '';
        if (is_array($commentText)) {
            $commentText = $commentText['text'] ?? '';
        }

        // Extract commenter info
        $commenterUrn = null;
        $commenterName = '';
        $commenterHeadline = '';
        $commenterProfileUrl = '';

        // Try various LinkedIn response structures
        if (isset($element['commenter'])) {
            $commenter = $element['commenter'];
            $commenterUrn = $commenter['commenterProfileId'] ?? $commenter['memberProfileUrn'] ?? $commenter['urn'] ?? null;
            $commenterName = trim(($commenter['firstName'] ?? '') . ' ' . ($commenter['lastName'] ?? ''));
            $commenterHeadline = $commenter['headline'] ?? '';
        }

        if (isset($element['actor'])) {
            $commenterUrn = $commenterUrn ?: ($element['actor']['urn'] ?? null);
            $commenterName = $commenterName ?: ($element['actor']['name']['text'] ?? '');
            $commenterHeadline = $commenterHeadline ?: ($element['actor']['description']['text'] ?? '');
        }

        // Build profile URL from URN
        if ($commenterUrn && empty($commenterProfileUrl)) {
            // Extract public ID if available from included data
            $publicId = $element['commenterProfileId'] ?? null;
            if ($publicId) {
                $commenterProfileUrl = 'https://www.linkedin.com/in/' . $publicId;
            }
        }

        $comments[] = [
            'comment_urn' => $commentUrn,
            'comment_text' => $commentText,
            'commenter_urn' => $commenterUrn,
            'commenter_name' => $commenterName,
            'commenter_headline' => $commenterHeadline,
            'commenter_profile_url' => $commenterProfileUrl,
        ];
    }

    echo "  Found " . count($comments) . " comments\n";

    // ---------------------------------------------------------------
    // Step 2: Insert new comments and check for keyword matches
    // ---------------------------------------------------------------
    foreach ($comments as $commentData) {
        if (empty($commentData['comment_urn'])) continue;

        // Dedup: skip if we already have this comment
        $existing = PostComment::findByCommentUrn($commentData['comment_urn']);
        if ($existing) continue;

        // Check keyword match (case-insensitive)
        $matched = stripos($commentData['comment_text'], $automation['trigger_keyword']) !== false ? 1 : 0;

        PostComment::create([
            'automation_id' => $automationId,
            'tenant_id' => $tenantId,
            'comment_urn' => $commentData['comment_urn'],
            'commenter_profile_url' => $commentData['commenter_profile_url'],
            'commenter_urn' => $commentData['commenter_urn'],
            'commenter_name' => $commentData['commenter_name'],
            'commenter_headline' => $commentData['commenter_headline'],
            'comment_text' => $commentData['comment_text'],
            'keyword_matched' => $matched,
        ]);

        PostAutomation::incrementStats($automationId, 'comments_detected');

        if ($matched) {
            PostAutomation::incrementStats($automationId, 'keyword_matches');
            echo "  Keyword match: {$commentData['commenter_name']} - \"{$commentData['comment_text']}\"\n";
        }
    }

    // ---------------------------------------------------------------
    // Step 3: Process pending replies (auto-reply to comments)
    // ---------------------------------------------------------------
    if ($automation['auto_reply_enabled']) {
        $pendingReplies = PostComment::pendingReplies($automationId);
        $repliesThisRun = 0;
        $maxRepliesPerRun = 20;

        foreach ($pendingReplies as $pending) {
            if ($repliesThisRun >= $maxRepliesPerRun) break;

            try {
                $result = $linkedin->postComment($postUrn, $automation['auto_reply_template'], $pending['comment_urn']);
                if ($result !== false) {
                    PostComment::markReplied($pending['id']);
                    PostAutomation::incrementStats($automationId, 'replies_sent');
                    $repliesThisRun++;
                    echo "  Replied to comment by {$pending['commenter_name']}\n";

                    // Log activity
                    $stmt = $db->prepare("
                        INSERT INTO connectpilot_activity_log (tenant_id, campaign_id, lead_id, action_type, message_sent, status)
                        VALUES (?, ?, NULL, 'comment_replied', ?, 'success')
                    ");
                    $stmt->execute([$tenantId, $automationId, $automation['auto_reply_template']]);
                }
            } catch (\Exception $e) {
                error_log("PostRunner: Error replying to comment {$pending['id']}: " . $e->getMessage());
            }

            // Random delay between replies
            sleep(rand(2, 8));
        }
    }

    // ---------------------------------------------------------------
    // Step 4: Process pending DMs
    // ---------------------------------------------------------------
    if ($automation['auto_dm_enabled']) {
        $pendingDMs = PostComment::pendingDMs($automationId);

        foreach ($pendingDMs as $pending) {
            // Respect daily message limits
            if ($messagesSentToday >= $dailyMessageLimit) {
                echo "  Daily message limit reached ({$dailyMessageLimit}), stopping DMs.\n";
                break;
            }

            if (empty($pending['commenter_urn'])) {
                echo "  No commenter URN for {$pending['commenter_name']}, skipping DM.\n";
                continue;
            }

            // Personalize DM template
            $firstName = explode(' ', $pending['commenter_name'] ?? '')[0] ?? '';
            $message = str_replace(
                ['{{first_name}}', '{{lead_magnet_url}}'],
                [$firstName, $leadMagnetUrl],
                $automation['dm_template'] ?? ''
            );

            try {
                $result = $linkedin->sendMessage($pending['commenter_urn'], $message);
                if ($result !== false) {
                    $messagesSentToday++;

                    // Create lead record from commenter profile data
                    $leadId = null;
                    $nameParts = explode(' ', $pending['commenter_name'] ?? '', 2);
                    $stmt = $db->prepare("
                        INSERT INTO linkedin_leads (tenant_id, campaign_id, first_name, last_name, headline, linkedin_profile_url, linkedin_id, connection_status, current_step, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'connected', 0, NOW())
                    ");
                    $stmt->execute([
                        $tenantId,
                        null, // No outbound campaign — this is inbound
                        $nameParts[0] ?? '',
                        $nameParts[1] ?? '',
                        $pending['commenter_headline'] ?? '',
                        $pending['commenter_profile_url'] ?? '',
                        $pending['commenter_urn'] ?? '',
                    ]);
                    $leadId = $db->lastInsertId();

                    PostComment::markDMSent($pending['id'], $leadId);
                    PostAutomation::incrementStats($automationId, 'dms_sent');
                    PostAutomation::incrementStats($automationId, 'leads_captured');

                    echo "  DM sent to {$pending['commenter_name']} (lead #{$leadId})\n";

                    // Log activity
                    $stmt = $db->prepare("
                        INSERT INTO connectpilot_activity_log (tenant_id, campaign_id, lead_id, action_type, message_sent, status)
                        VALUES (?, ?, ?, 'dm_sent_auto', ?, 'success')
                    ");
                    $stmt->execute([$tenantId, $automationId, $leadId, $message]);
                }
            } catch (\Exception $e) {
                error_log("PostRunner: Error sending DM to {$pending['commenter_name']}: " . $e->getMessage());

                // Log failed DM
                $stmt = $db->prepare("
                    INSERT INTO connectpilot_activity_log (tenant_id, campaign_id, lead_id, action_type, message_sent, status, error_message)
                    VALUES (?, ?, NULL, 'dm_sent_auto', ?, 'failed', ?)
                ");
                $stmt->execute([$tenantId, $automationId, $message, $e->getMessage()]);
            }

            // Random delay between DMs
            sleep(rand(2, 8));
        }

        // Update daily message counter
        $stmt = $db->prepare("UPDATE linkedin_accounts SET messages_sent_today = ?, last_activity_at = NOW() WHERE id = ?");
        $stmt->execute([$messagesSentToday, $automation['account_id']]);
    }

    // Update last checked timestamp
    PostAutomation::updateLastChecked($automationId);
}

echo "Post automation runner completed at " . date('Y-m-d H:i:s') . "\n";
