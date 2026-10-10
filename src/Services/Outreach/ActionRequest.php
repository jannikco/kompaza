<?php

namespace App\Services\Outreach;

/**
 * One governed LinkedIn write. Built by cron, the admin UI, or (later) MCP.
 *
 * idempotency_key is sha256(account|action|subject|body) so a retried cron tick
 * or MCP call returns the prior result instead of sending twice.
 */
final class ActionRequest
{
    public function __construct(
        public readonly int $tenantId,
        public readonly int $accountId,
        public readonly string $actionType,
        public readonly string $channel,
        public readonly ?string $subjectUrn = null,
        public readonly ?string $subjectLabel = null,
        public readonly ?string $targetUrn = null,
        public readonly ?string $targetUrl = null,
        public readonly ?string $bodyText = null,
        public readonly string $source = 'cron',
        public readonly ?string $sourceRef = null,
        public readonly ?string $toolName = null,
        public readonly ?int $leadId = null,
        public readonly ?int $automationId = null,
        public readonly ?int $userId = null,
        public readonly array $payload = [],
    ) {
    }

    public function idempotencyKey(): string
    {
        return hash('sha256', implode('|', [
            (string) $this->accountId,
            $this->actionType,
            (string) $this->subjectUrn,
            (string) $this->bodyText,
        ]));
    }
}
