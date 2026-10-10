<?php

namespace App\Models;

use App\Database\Database;

class OutreachAction
{
    public static function findByIdempotency(string $key): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM outreach_actions WHERE idempotency_key = ? LIMIT 1");
        $stmt->execute([$key]);
        return $stmt->fetch() ?: null;
    }

    public static function record(array $data): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO outreach_actions
                (tenant_id, linkedin_account_id, user_id, source, source_ref, tool_name,
                 channel, action_type, subject_urn, subject_label, target_urn, target_url,
                 body_text, lead_id, automation_id, result, refusal_code, error_message,
                 response_urn, endpoint_key, execution_mode, http_status, duration_ms,
                 idempotency_key, request_payload, created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW())
        ");
        $payload = $data['request_payload'] ?? null;
        if (is_array($payload)) {
            $payload = json_encode($payload, JSON_UNESCAPED_SLASHES);
        }
        $stmt->execute([
            $data['tenant_id'],
            $data['linkedin_account_id'] ?? null,
            $data['user_id'] ?? null,
            $data['source'] ?? 'cron',
            $data['source_ref'] ?? null,
            $data['tool_name'] ?? null,
            $data['channel'] ?? 'none',
            $data['action_type'],
            $data['subject_urn'] ?? null,
            $data['subject_label'] ?? null,
            $data['target_urn'] ?? null,
            $data['target_url'] ?? null,
            $data['body_text'] ?? null,
            $data['lead_id'] ?? null,
            $data['automation_id'] ?? null,
            $data['result'],
            $data['refusal_code'] ?? null,
            $data['error_message'] ?? null,
            $data['response_urn'] ?? null,
            $data['endpoint_key'] ?? null,
            $data['execution_mode'] ?? 'server',
            $data['http_status'] ?? null,
            $data['duration_ms'] ?? null,
            $data['idempotency_key'] ?? null,
            $payload,
        ]);
        return (int) $db->lastInsertId();
    }

    public static function recentByTenant(int $tenantId, int $limit = 50): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM outreach_actions
             WHERE tenant_id = ?
             ORDER BY created_at DESC
             LIMIT ?
        ");
        $stmt->execute([$tenantId, $limit]);
        return $stmt->fetchAll();
    }
}
