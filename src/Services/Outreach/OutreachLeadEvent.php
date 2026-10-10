<?php

namespace App\Services\Outreach;

use App\Database\Database;
use App\Models\LinkedInLead;

/**
 * Writes one row to outreach_lead_events and flips the matching denormalised
 * flag on linkedin_leads in the same transaction.
 */
final class OutreachLeadEvent
{
    private const FLAG_BY_TYPE = [
        'comment'                 => 'engaged_your_content',
        'reaction'                => 'engaged_your_content',
        'repost'                  => 'engaged_your_content',
        'lead_magnet_click'       => 'checked_resource',
        'email_captured'          => 'submitted_email',
        'dm_sent'                 => 'we_dmed',
        'follow_up_sent'          => 'we_dmed',
        'comment_reply'           => 'we_replied_to_comment',
        'connection_sent'         => 'we_sent_connection_request',
        'connection_accepted'     => 'we_sent_connection_request',
    ];

    private const WEIGHT_BY_TYPE = [
        'comment'               => 5.0,
        'reaction'              => 1.0,
        'repost'                => 4.0,
        'lead_magnet_click'     => 8.0,
        'email_captured'        => 12.0,
        'dm_sent'               => 0.0,
        'follow_up_sent'        => 0.0,
        'comment_reply'         => 0.0,
        'connection_sent'       => 0.0,
        'connection_accepted'   => 3.0,
    ];

    public static function record(
        int $tenantId,
        string $eventType,
        ?int $leadId = null,
        ?int $accountId = null,
        ?string $subjectUrn = null,
        ?int $actionId = null,
        ?string $source = null,
        ?string $sourceRef = null,
        array $payload = [],
    ): void {
        $db = Database::getConnection();
        $weight = self::WEIGHT_BY_TYPE[$eventType] ?? 0.0;

        $db->beginTransaction();
        try {
            $stmt = $db->prepare("
                INSERT INTO outreach_lead_events
                    (tenant_id, lead_id, linkedin_account_id, subject_urn, event_type,
                     weight, source, source_ref, action_id, payload_json, occurred_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $tenantId,
                $leadId,
                $accountId,
                $subjectUrn,
                $eventType,
                $weight,
                $source,
                $sourceRef,
                $actionId,
                $payload !== [] ? json_encode($payload, JSON_UNESCAPED_SLASHES) : null,
            ]);

            if ($leadId) {
                $flag = self::FLAG_BY_TYPE[$eventType] ?? null;
                $sets = [
                    'has_history = 1',
                    'last_event_at = NOW()',
                    'first_touch_at = COALESCE(first_touch_at, NOW())',
                    'heat_score = heat_score + ?',
                ];
                $params = [$weight];
                if ($flag) {
                    $sets[] = "`{$flag}` = 1";
                }
                $sql = 'UPDATE linkedin_leads SET ' . implode(', ', $sets) . ' WHERE id = ? AND tenant_id = ?';
                $params[] = $leadId;
                $params[] = $tenantId;
                $db->prepare($sql)->execute($params);

                if ($eventType === 'dm_sent' || $eventType === 'follow_up_sent' || $eventType === 'connection_sent') {
                    LinkedInLead::update($leadId, ['last_contacted_at' => date('Y-m-d H:i:s')]);
                }
            }

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log('OutreachLeadEvent::record failed: ' . $e->getMessage());
        }
    }
}
