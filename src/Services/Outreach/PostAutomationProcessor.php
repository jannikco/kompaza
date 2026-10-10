<?php

namespace App\Services\Outreach;

use App\Database\Database;
use App\Models\LinkedInLead;
use App\Models\PostAutomation;
use App\Models\PostComment;
use App\Services\LinkedIn\CommentParser;
use App\Services\LinkedInService;

/**
 * One cron pass over a single comment-to-DM automation.
 *
 * Writes go through ActionExecutor so they share limits, do-not-engage,
 * emergency stop and the action log with every other Outreach caller.
 */
final class PostAutomationProcessor
{
    private const MAX_PER_STEP = 20;

    public function __construct(
        private readonly ActionExecutor $executor = new ActionExecutor(),
    ) {
    }

    public function process(array $automation): void
    {
        $automationId = (int) $automation['id'];
        $tenantId = (int) $automation['tenant_id'];
        $accountId = (int) $automation['account_id'];
        $postUrn = $automation['post_urn'] ?? '';

        if ($postUrn === '') {
            error_log("PostRunner: Automation {$automationId} has no post_urn, skipping.");
            return;
        }

        if (!empty($automation['emergency_stop'])) {
            echo "  Account {$accountId} is emergency-stopped; capturing comments only.\n";
        }

        try {
            $linkedin = LinkedInService::forAccount($accountId);
        } catch (\Throwable $e) {
            error_log("PostRunner: no usable session for account {$accountId}: " . $e->getMessage());
            PostAutomation::updateLastChecked($automationId);
            return;
        }

        $response = $linkedin->getPostComments($postUrn, 0, 50);
        if ($response === false) {
            if (in_array($linkedin->lastHttpStatus(), [401, 403], true)) {
                \App\Models\LinkedInAccount::markAuthFailure($accountId, 'comment poll');
            }
            error_log("PostRunner: Failed to fetch comments for automation {$automationId}");
            PostAutomation::updateLastChecked($automationId);
            return;
        }

        $parsed = CommentParser::parse($response);
        $comments = $parsed['comments'];
        echo "  Found " . count($comments) . " comments"
            . " ({$parsed['resolved']} with a profile identifier, {$parsed['unresolved']} without)\n";

        $keywords = PostAutomation::keywords($automation);
        $this->ingestComments($automation, $comments, $keywords);

        $leadMagnetUrl = $this->leadMagnetUrl($automation);

        if (!empty($automation['auto_like_enabled'])) {
            $this->sendLikes($automation, $postUrn);
        }
        if (!empty($automation['auto_reply_enabled'])) {
            $this->sendReplies($automation, $postUrn);
        }
        if (!empty($automation['auto_connect_enabled'])) {
            $this->sendConnects($automation);
        }
        if (!empty($automation['auto_dm_enabled'])) {
            $this->sendDms($automation, $leadMagnetUrl);
        }
        if (!empty($automation['follow_up_enabled'])) {
            $this->sendFollowUps($automation, $leadMagnetUrl);
        }

        PostAutomation::updateLastChecked($automationId);
    }

    /** @param list<array> $comments */
    private function ingestComments(array $automation, array $comments, array $keywords): void
    {
        $automationId = (int) $automation['id'];
        $tenantId = (int) $automation['tenant_id'];

        foreach ($comments as $comment) {
            if (empty($comment['comment_urn'])) {
                continue;
            }
            if (PostComment::findByCommentUrn($comment['comment_urn'])) {
                continue;
            }

            $distance = $comment['commenter_distance'] ?? null;
            $isSelf = is_string($distance) && strtoupper($distance) === 'SELF';
            $matched = $isSelf ? null : KeywordMatcher::firstMatch((string) ($comment['comment_text'] ?? ''), $keywords);

            $skip = null;
            if ($isSelf) {
                $skip = 'self';
            } elseif (!empty($automation['partially_engage']) && $this->alreadyRepliedInThread($comments, $comment)) {
                $skip = 'already_replied';
            }

            PostComment::create([
                'automation_id'          => $automationId,
                'tenant_id'              => $tenantId,
                'comment_urn'            => $comment['comment_urn'],
                'commenter_profile_url'  => $comment['commenter_profile_url'] ?? '',
                'commenter_urn'          => $comment['commenter_urn'] ?? null,
                'commenter_name'         => $comment['commenter_name'] ?? '',
                'commenter_headline'     => $comment['commenter_headline'] ?? '',
                'commenter_distance'     => $distance,
                'comment_text'           => $comment['comment_text'] ?? '',
                'keyword_matched'        => $matched !== null ? 1 : 0,
                'matched_keyword'        => $matched,
                'skipped_reason'         => $skip,
            ]);

            PostAutomation::incrementStats($automationId, 'comments_detected');
            if ($matched !== null) {
                PostAutomation::incrementStats($automationId, 'keyword_matches');
                echo "  Keyword match ({$matched}): {$comment['commenter_name']} — \"{$comment['comment_text']}\"\n";
            }
        }
    }

    private function alreadyRepliedInThread(array $all, array $comment): bool
    {
        $parent = $comment['comment_urn'] ?? '';
        if ($parent === '') {
            return false;
        }
        foreach ($all as $other) {
            $distance = strtoupper((string) ($other['commenter_distance'] ?? ''));
            if ($distance !== 'SELF') {
                continue;
            }
            // Best-effort: a SELF comment that arrived after this one in the same fetch
            // is treated as our reply. Voyager does not always parent-link replies.
            if (($other['commented_at'] ?? '') >= ($comment['commented_at'] ?? '')) {
                return true;
            }
        }
        return false;
    }

    private function sendLikes(array $automation, string $postUrn): void
    {
        $n = 0;
        foreach (PostComment::pendingLikes((int) $automation['id']) as $row) {
            if ($n >= self::MAX_PER_STEP) {
                break;
            }
            $thread = $row['comment_urn'] ?: $postUrn;
            $result = $this->executor->execute(new ActionRequest(
                tenantId: (int) $automation['tenant_id'],
                accountId: (int) $automation['account_id'],
                actionType: 'react',
                channel: 'reaction',
                subjectUrn: $thread,
                subjectLabel: $row['commenter_name'] ?? null,
                source: 'cron',
                sourceRef: 'automation:' . $automation['id'],
                toolName: 'auto_like',
                automationId: (int) $automation['id'],
            ));
            if ($result->ok()) {
                PostComment::markLiked((int) $row['id']);
                PostAutomation::incrementStats((int) $automation['id'], 'likes_sent');
                $n++;
                echo "  Liked comment by {$row['commenter_name']}\n";
            } elseif ($result->refusalCode === 'limit_reached' || $result->refusalCode === 'emergency_stop') {
                break;
            }
        }
    }

    private function sendReplies(array $automation, string $postUrn): void
    {
        $n = 0;
        foreach (PostComment::pendingReplies((int) $automation['id']) as $row) {
            if ($n >= self::MAX_PER_STEP) {
                break;
            }
            if (($row['skipped_reason'] ?? '') === 'already_replied') {
                PostComment::markReplied((int) $row['id']);
                continue;
            }

            $firstDegree = $this->isFirstDegree($row['commenter_distance'] ?? null);
            $template = $firstDegree
                ? (string) ($automation['auto_reply_template'] ?? '')
                : (string) (($automation['non_first_degree_reply_template'] ?: $automation['auto_reply_template']) ?? '');
            if (trim($template) === '') {
                continue;
            }

            $vars = $this->templateVars($row, '');
            $text = TemplateRenderer::render($template, $vars);
            $mentions = TemplateRenderer::mentionOffsets($text, $vars);
            $channel = $firstDegree ? 'connected_reply' : 'non_connected_reply';

            $result = $this->executor->execute(new ActionRequest(
                tenantId: (int) $automation['tenant_id'],
                accountId: (int) $automation['account_id'],
                actionType: 'comment_reply',
                channel: $channel,
                subjectUrn: $row['commenter_urn'] ?? null,
                subjectLabel: $row['commenter_name'] ?? null,
                targetUrn: $postUrn,
                bodyText: $text,
                source: 'cron',
                sourceRef: 'automation:' . $automation['id'],
                toolName: 'auto_reply',
                automationId: (int) $automation['id'],
                payload: [
                    'parent_comment_urn' => $row['comment_urn'],
                    'mentions' => $mentions,
                    'public_identifier' => $this->publicIdFromUrl($row['commenter_profile_url'] ?? ''),
                ],
            ));

            if ($result->ok()) {
                PostComment::markReplied((int) $row['id']);
                PostAutomation::incrementStats((int) $automation['id'], 'replies_sent');
                $n++;
                echo "  Replied to {$row['commenter_name']}\n";
            } elseif ($result->refusalCode === 'limit_reached' || $result->refusalCode === 'emergency_stop') {
                break;
            }
        }
    }

    private function sendConnects(array $automation): void
    {
        $n = 0;
        foreach (PostComment::pendingConnects((int) $automation['id']) as $row) {
            if ($n >= self::MAX_PER_STEP) {
                break;
            }
            if (empty($row['commenter_urn'])) {
                continue;
            }
            $result = $this->executor->execute(new ActionRequest(
                tenantId: (int) $automation['tenant_id'],
                accountId: (int) $automation['account_id'],
                actionType: 'connect',
                channel: 'connection_daily',
                subjectUrn: $row['commenter_urn'],
                subjectLabel: $row['commenter_name'] ?? null,
                targetUrl: $row['commenter_profile_url'] ?? null,
                source: 'cron',
                sourceRef: 'automation:' . $automation['id'],
                toolName: 'auto_connect',
                automationId: (int) $automation['id'],
                payload: [
                    'public_identifier' => $this->publicIdFromUrl($row['commenter_profile_url'] ?? ''),
                ],
            ));
            if ($result->ok()) {
                PostComment::markConnectSent((int) $row['id']);
                PostAutomation::incrementStats((int) $automation['id'], 'connections_sent');
                $n++;
                echo "  Connection request sent to {$row['commenter_name']}\n";
            } elseif ($result->refusalCode === 'limit_reached' || $result->refusalCode === 'emergency_stop') {
                break;
            }
        }
    }

    private function sendDms(array $automation, string $leadMagnetUrl): void
    {
        $n = 0;
        $ordinal = (int) ($automation['dms_sent'] ?? 0);
        foreach (PostComment::pendingDMs((int) $automation['id']) as $row) {
            if ($n >= self::MAX_PER_STEP) {
                break;
            }
            if (empty($row['commenter_urn'])) {
                echo "  No commenter URN for {$row['commenter_name']}, skipping DM.\n";
                continue;
            }

            $firstDegree = $this->isFirstDegree($row['commenter_distance'] ?? null);
            if (!$firstDegree && empty($automation['auto_connect_enabled']) && empty($row['connect_sent'])) {
                continue;
            }
            if (!$firstDegree && !empty($automation['auto_connect_enabled']) && empty($row['connect_sent'])) {
                // Connect step runs first in the same tick; if it just fired,
                // wait for accept before DMing.
                continue;
            }

            $template = PostAutomation::pickDmTemplate($automation, $ordinal);
            if (trim($template) === '') {
                continue;
            }
            $text = TemplateRenderer::render($template, $this->templateVars($row, $leadMagnetUrl));

            $result = $this->executor->execute(new ActionRequest(
                tenantId: (int) $automation['tenant_id'],
                accountId: (int) $automation['account_id'],
                actionType: 'message',
                channel: 'dm',
                subjectUrn: $row['commenter_urn'],
                subjectLabel: $row['commenter_name'] ?? null,
                targetUrl: $row['commenter_profile_url'] ?? null,
                bodyText: $text,
                source: 'cron',
                sourceRef: 'automation:' . $automation['id'],
                toolName: 'auto_dm',
                automationId: (int) $automation['id'],
                payload: [
                    'public_identifier' => $this->publicIdFromUrl($row['commenter_profile_url'] ?? ''),
                ],
            ));

            if ($result->ok()) {
                $leadId = $this->upsertLead($automation, $row, $firstDegree);
                PostComment::markDMSent((int) $row['id'], $leadId ?: null);
                if (!empty($automation['follow_up_enabled'])) {
                    PostComment::scheduleFollowUp((int) $row['id'], (int) ($automation['follow_up_delay_minutes'] ?? 120));
                }
                PostAutomation::incrementStats((int) $automation['id'], 'dms_sent');
                if ($leadId) {
                    PostAutomation::incrementStats((int) $automation['id'], 'leads_captured');
                }
                $n++;
                $ordinal++;
                echo "  DM sent to {$row['commenter_name']}" . ($leadId ? " (lead #{$leadId})" : '') . "\n";
            } elseif ($result->refusalCode === 'limit_reached' || $result->refusalCode === 'emergency_stop') {
                echo "  Daily DM budget reached; stopping DMs.\n";
                break;
            }
        }
    }

    private function sendFollowUps(array $automation, string $leadMagnetUrl): void
    {
        $template = (string) ($automation['follow_up_template'] ?? '');
        if (trim($template) === '') {
            return;
        }

        $n = 0;
        foreach (PostComment::pendingFollowUps((int) $automation['id']) as $row) {
            if ($n >= self::MAX_PER_STEP) {
                break;
            }
            if (!empty($automation['follow_up_only_if_no_response']) && $this->leadHasReplied($row['lead_id'] ?? null)) {
                PostComment::markFollowUpSent((int) $row['id']);
                continue;
            }
            if (empty($row['commenter_urn'])) {
                continue;
            }

            $text = TemplateRenderer::render($template, $this->templateVars($row, $leadMagnetUrl));
            $result = $this->executor->execute(new ActionRequest(
                tenantId: (int) $automation['tenant_id'],
                accountId: (int) $automation['account_id'],
                actionType: 'follow_up',
                channel: 'dm',
                subjectUrn: $row['commenter_urn'],
                subjectLabel: $row['commenter_name'] ?? null,
                bodyText: $text,
                source: 'cron',
                sourceRef: 'automation:' . $automation['id'],
                toolName: 'follow_up_dm',
                leadId: $row['lead_id'] ? (int) $row['lead_id'] : null,
                automationId: (int) $automation['id'],
            ));

            if ($result->ok()) {
                PostComment::markFollowUpSent((int) $row['id']);
                PostAutomation::incrementStats((int) $automation['id'], 'follow_ups_sent');
                $n++;
                echo "  Follow-up DM sent to {$row['commenter_name']}\n";
            } elseif ($result->refusalCode === 'limit_reached' || $result->refusalCode === 'emergency_stop') {
                break;
            }
        }
    }

    private function upsertLead(array $automation, array $row, bool $firstDegree): int
    {
        $nameParts = explode(' ', (string) ($row['commenter_name'] ?? ''), 2);
        $publicId = $this->publicIdFromUrl($row['commenter_profile_url'] ?? '');

        return LinkedInLead::upsertByUrn((int) $automation['tenant_id'], [
            'first_name'           => $nameParts[0] ?? '',
            'last_name'            => $nameParts[1] ?? '',
            'headline'             => $row['commenter_headline'] ?? '',
            'linkedin_profile_url' => $row['commenter_profile_url'] ?? '',
            'linkedin_id'          => $row['commenter_urn'] ?? '',
            'linkedin_username'    => $publicId,
            'member_urn'           => $row['commenter_urn'] ?? null,
            'connection_status'    => $firstDegree ? 'accepted' : ($row['connect_sent'] ? 'pending' : 'none'),
            'network_distance'     => $firstDegree ? 'DISTANCE_1' : ($row['commenter_distance'] ?? null),
            'status'               => 'contacted',
            'source'               => 'automation',
        ]);
    }

    private function leadHasReplied(mixed $leadId): bool
    {
        if (!$leadId) {
            return false;
        }
        $lead = LinkedInLead::find((int) $leadId);
        return $lead && !empty($lead['last_replied_at']);
    }

    private function isFirstDegree(?string $distance): bool
    {
        if ($distance === null || $distance === '') {
            return false;
        }
        $d = strtoupper($distance);
        return in_array($d, ['DISTANCE_1', 'FIRST_DEGREE', 'SELF'], true);
    }

    /** @return array<string,string> */
    private function templateVars(array $row, string $leadMagnetUrl): array
    {
        $name = trim((string) ($row['commenter_name'] ?? ''));
        $parts = $name === '' ? [''] : explode(' ', $name, 2);
        return [
            'first_name'          => $parts[0] ?? '',
            'last_name'           => $parts[1] ?? '',
            'full_name'           => $name,
            'linkedin_username'   => $this->publicIdFromUrl($row['commenter_profile_url'] ?? '') ?? '',
            'lead_magnet_url'     => $leadMagnetUrl,
            'commenter_urn'       => (string) ($row['commenter_urn'] ?? ''),
        ];
    }

    private function publicIdFromUrl(string $url): ?string
    {
        if (preg_match('#/in/([^/?#]+)#', $url, $m)) {
            return rtrim($m[1], '/');
        }
        return null;
    }

    private function leadMagnetUrl(array $automation): string
    {
        if (empty($automation['lead_magnet_id'])) {
            return '';
        }
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT slug FROM lead_magnets WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$automation['lead_magnet_id'], $automation['tenant_id']]);
        $lm = $stmt->fetch();
        if (!$lm) {
            return '';
        }
        $stmt = $db->prepare("SELECT slug, custom_domain FROM tenants WHERE id = ?");
        $stmt->execute([$automation['tenant_id']]);
        $tenant = $stmt->fetch();
        if (!$tenant) {
            return '';
        }
        $host = $tenant['custom_domain'] ?: ($tenant['slug'] . '.' . PLATFORM_DOMAIN);
        return 'https://' . $host . '/lp/' . $lm['slug'];
    }
}
