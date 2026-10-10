<?php

namespace App\Services\Outreach;

final class ActionResult
{
    public function __construct(
        public readonly string $result,
        public readonly ?string $refusalCode = null,
        public readonly ?string $errorMessage = null,
        public readonly ?string $responseUrn = null,
        public readonly mixed $raw = null,
        public readonly ?int $actionId = null,
    ) {
    }

    public function ok(): bool
    {
        return $this->result === 'ok';
    }

    public static function refused(string $code, string $message = ''): self
    {
        return new self('refused', $code, $message !== '' ? $message : $code);
    }

    public static function error(string $message, mixed $raw = null): self
    {
        return new self('error', null, $message, null, $raw);
    }

    public static function queued(int $pendingId): self
    {
        return new self('queued', null, 'Queued for approval', null, ['pending_action_id' => $pendingId]);
    }

    public static function okResult(mixed $raw = null, ?string $responseUrn = null, ?int $actionId = null): self
    {
        return new self('ok', null, null, $responseUrn, $raw, $actionId);
    }
}
