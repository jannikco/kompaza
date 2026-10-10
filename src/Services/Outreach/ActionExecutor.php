<?php

namespace App\Services\Outreach;

use App\Database\Database;
use App\Models\LinkedInAccount;
use App\Models\OutreachAction;
use App\Models\OutreachActivityLimit;
use App\Services\LinkedInService;

/**
 * The only write path for LinkedIn actions.
 *
 * Cron, admin UI and (later) MCP all build an ActionRequest and call execute().
 * Policy is ActionPolicy (pure). Transport is LinkedInService.
 */
final class ActionExecutor
{
    public function execute(ActionRequest $req): ActionResult
    {
        $started = hrtime(true);
        $key = $req->idempotencyKey();

        $prior = OutreachAction::findByIdempotency($key);
        if ($prior && in_array($prior['result'], ['ok', 'refused', 'queued'], true)) {
            return new ActionResult(
                $prior['result'],
                $prior['refusal_code'],
                $prior['error_message'],
                $prior['response_urn'],
                null,
                (int) $prior['id'],
            );
        }

        $account = LinkedInAccount::find($req->accountId);
        if (!$account || (int) $account['tenant_id'] !== $req->tenantId) {
            return $this->commit($req, $key, ActionResult::refused('account_missing'), $started);
        }

        OutreachActivityLimit::seedForAccount($req->tenantId, $req->accountId, $account);
        $limit = OutreachActivityLimit::forChannel($req->accountId, $req->channel);
        $blocked = $this->isDoNotEngage($req);

        $refusal = ActionPolicy::evaluate(
            $account,
            $req->channel,
            $req->source,
            $blocked,
            $limit,
        );

        if ($refusal === 'needs_approval') {
            $pendingId = $this->enqueue($req);
            $result = ActionResult::queued($pendingId);
            return $this->commit($req, $key, $result, $started);
        }

        if ($refusal !== null) {
            return $this->commit($req, $key, ActionResult::refused($refusal), $started);
        }

        try {
            $linkedin = LinkedInService::forAccount($req->accountId);
        } catch (\Throwable $e) {
            return $this->commit($req, $key, ActionResult::error($e->getMessage()), $started);
        }

        $raw = $this->dispatch($linkedin, $req);
        $http = $linkedin->lastHttpStatus();

        if ($raw === false) {
            if (in_array($http, [401, 403], true)) {
                LinkedInAccount::markAuthFailure($req->accountId, "HTTP {$http} on {$req->actionType}");
            }
            $result = ActionResult::error(
                $linkedin->lastError() ?: "LinkedIn {$req->actionType} failed (HTTP {$http})",
                ['http_status' => $http],
            );
            return $this->commit($req, $key, $result, $started, $http, 'server');
        }

        LinkedInAccount::markActive($req->accountId);

        $cooldown = (int) ($limit['cooldown_seconds'] ?? 0);
        OutreachActivityLimit::spend($req->accountId, $req->channel, $cooldown);
        if ($req->channel === 'connection_daily') {
            OutreachActivityLimit::spend($req->accountId, 'connection_weekly', 0);
        }

        $responseUrn = is_array($raw) ? ($raw['entityUrn'] ?? $raw['data']['entityUrn'] ?? null) : null;
        $ok = ActionResult::okResult($raw, is_string($responseUrn) ? $responseUrn : null);
        $committed = $this->commit($req, $key, $ok, $started, $http, 'server');

        $this->recordLeadEvent($req, $committed->actionId);
        $this->bumpLegacyCounters($req);

        return $committed;
    }

    private function dispatch(LinkedInService $linkedin, ActionRequest $req): array|false
    {
        return match ($req->actionType) {
            'comment', 'comment_reply' => $linkedin->postComment(
                $req->targetUrn ?: (string) $req->subjectUrn,
                (string) $req->bodyText,
                $req->payload['parent_comment_urn'] ?? null,
                $req->payload['mentions'] ?? [],
            ),
            'react' => $linkedin->reactToPost(
                (string) $req->subjectUrn,
                (string) ($req->payload['reaction'] ?? 'LIKE'),
            ),
            'connect' => $linkedin->sendConnectionRequest(
                (string) $req->subjectUrn,
                (string) ($req->bodyText ?? ''),
            ),
            'message', 'follow_up', 'inmail' => $linkedin->sendMessage(
                (string) $req->subjectUrn,
                (string) $req->bodyText,
            ),
            'view_profile' => $linkedin->viewProfile((string) ($req->targetUrl ?? $req->subjectUrn)),
            default => false,
        };
    }

    private function isDoNotEngage(ActionRequest $req): bool
    {
        if (!$req->subjectUrn && empty($req->payload['public_identifier'])) {
            return false;
        }
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT 1 FROM outreach_do_not_engage
             WHERE tenant_id = ?
               AND (
                    (subject_urn IS NOT NULL AND subject_urn = ?)
                 OR (public_identifier IS NOT NULL AND public_identifier = ?)
               )
             LIMIT 1
        ");
        $stmt->execute([
            $req->tenantId,
            $req->subjectUrn,
            $req->payload['public_identifier'] ?? null,
        ]);
        return (bool) $stmt->fetchColumn();
    }

    private function enqueue(ActionRequest $req): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO outreach_pending_actions
                (tenant_id, linkedin_account_id, channel, action_type, subject_urn,
                 subject_label, target_urn, payload_json, preview_text, requested_via,
                 requested_by_user_id, status, expires_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', DATE_ADD(NOW(), INTERVAL 7 DAY))
        ");
        $stmt->execute([
            $req->tenantId,
            $req->accountId,
            $req->channel,
            $req->actionType,
            $req->subjectUrn,
            $req->subjectLabel,
            $req->targetUrn,
            json_encode($req->payload + [
                'body_text' => $req->bodyText,
                'target_url' => $req->targetUrl,
                'lead_id' => $req->leadId,
                'automation_id' => $req->automationId,
            ], JSON_UNESCAPED_SLASHES),
            $req->bodyText,
            $req->source === 'admin' ? 'admin' : ($req->source === 'rest' ? 'rest' : 'mcp'),
            $req->userId,
        ]);
        return (int) $db->lastInsertId();
    }

    private function commit(
        ActionRequest $req,
        string $key,
        ActionResult $result,
        int $started,
        ?int $http = null,
        ?string $mode = null,
    ): ActionResult {
        $ms = (int) ((hrtime(true) - $started) / 1_000_000);
        $id = OutreachAction::record([
            'tenant_id'            => $req->tenantId,
            'linkedin_account_id'  => $req->accountId,
            'user_id'              => $req->userId,
            'source'               => $req->source,
            'source_ref'           => $req->sourceRef,
            'tool_name'            => $req->toolName,
            'channel'              => $req->channel,
            'action_type'          => $req->actionType,
            'subject_urn'          => $req->subjectUrn,
            'subject_label'        => $req->subjectLabel,
            'target_urn'           => $req->targetUrn,
            'target_url'           => $req->targetUrl,
            'body_text'            => $req->bodyText,
            'lead_id'              => $req->leadId,
            'automation_id'        => $req->automationId,
            'result'               => $result->result,
            'refusal_code'         => $result->refusalCode,
            'error_message'        => $result->errorMessage,
            'response_urn'         => $result->responseUrn,
            'endpoint_key'         => $req->actionType,
            'execution_mode'       => $mode,
            'http_status'          => $http,
            'duration_ms'          => $ms,
            'idempotency_key'      => $key,
            'request_payload'      => $req->payload,
        ]);

        return new ActionResult(
            $result->result,
            $result->refusalCode,
            $result->errorMessage,
            $result->responseUrn,
            $result->raw,
            $id,
        );
    }

    private function recordLeadEvent(ActionRequest $req, ?int $actionId): void
    {
        $type = match ($req->actionType) {
            'message'        => 'dm_sent',
            'follow_up'      => 'follow_up_sent',
            'comment', 'comment_reply' => 'comment_reply',
            'connect'        => 'connection_sent',
            'react'          => 'reaction',
            default          => null,
        };
        if ($type === null) {
            return;
        }
        OutreachLeadEvent::record(
            $req->tenantId,
            $type,
            $req->leadId,
            $req->accountId,
            $req->subjectUrn,
            $actionId,
            $req->source,
            $req->sourceRef,
        );
    }

    private function bumpLegacyCounters(ActionRequest $req): void
    {
        $db = Database::getConnection();
        if (in_array($req->actionType, ['message', 'follow_up', 'inmail'], true)) {
            $db->prepare("
                UPDATE linkedin_accounts
                   SET messages_sent_today = messages_sent_today + 1, last_activity_at = NOW()
                 WHERE id = ?
            ")->execute([$req->accountId]);
        }
        if ($req->actionType === 'connect') {
            $db->prepare("
                UPDATE linkedin_accounts
                   SET connections_sent_today = connections_sent_today + 1, last_activity_at = NOW()
                 WHERE id = ?
            ")->execute([$req->accountId]);
        }
    }
}
