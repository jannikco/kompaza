<?php

namespace App\Services\Outreach;

/**
 * Pure decision table for a LinkedIn write. No I/O — ActionExecutor loads the
 * rows and asks this class whether the write may leave the building.
 *
 * Order matches migration 036:
 *   health → emergency stop → do-not-engage → limits → off-hours → pacing → approval
 */
final class ActionPolicy
{
    public const WRITE_CHANNELS = [
        'dm', 'inmail', 'connected_reply', 'non_connected_reply',
        'connection_daily', 'connection_weekly', 'comment', 'reaction', 'profile_view',
    ];

    /**
     * @param array{
     *   status?:string, emergency_stop?:int|bool, daily_message_limit?:int,
     *   safety_mode?:string, respect_off_hours?:int|bool, working_hours_json?:?string,
     *   timezone?:?string
     * } $account
     * @param array{
     *   limit_value?:int, used?:int, is_paused?:int|bool, is_readonly?:int|bool,
     *   approval_mode?:string, next_action_at?:?string, window_start?:?string, period?:string
     * }|null $limit
     * @return string|null refusal code, or null when the write may proceed
     */
    public static function evaluate(
        array $account,
        string $channel,
        string $source,
        bool $doNotEngage,
        ?array $limit,
        \DateTimeImmutable $now = new \DateTimeImmutable('now'),
    ): ?string {
        $status = (string) ($account['status'] ?? '');
        if ($status !== '' && $status !== 'active') {
            return 'account_unhealthy';
        }

        if (!empty($account['emergency_stop'])) {
            return 'emergency_stop';
        }

        $dmCap = array_key_exists('daily_message_limit', $account)
            ? (int) $account['daily_message_limit']
            : null;
        if ($dmCap === 0 && in_array($channel, ['dm', 'connected_reply', 'inmail'], true)) {
            return 'emergency_stop';
        }

        if ($doNotEngage) {
            return 'do_not_engage';
        }

        if ($limit !== null) {
            if (!empty($limit['is_paused'])) {
                return 'channel_paused';
            }
            $cap = (int) ($limit['limit_value'] ?? 0);
            if ($cap === 0 && empty($limit['is_readonly'])) {
                return 'channel_stopped';
            }
            if (empty($limit['is_readonly']) && $cap > 0 && (int) ($limit['used'] ?? 0) >= $cap) {
                return 'limit_reached';
            }
            $next = $limit['next_action_at'] ?? null;
            if (is_string($next) && $next !== '' && strtotime($next) > $now->getTimestamp()) {
                return 'cooldown';
            }
        }

        if (!empty($account['respect_off_hours']) && self::isOffHours($account, $now)) {
            return 'off_hours';
        }

        $safety = (string) ($account['safety_mode'] ?? 'auto');
        if ($safety === 'read_only' && $source !== 'cron') {
            return 'read_only';
        }

        $needsQueue = $safety === 'queue'
            || (($limit['approval_mode'] ?? 'auto') === 'queue');
        if ($needsQueue && in_array($source, ['mcp', 'admin', 'rest'], true)) {
            return 'needs_approval';
        }

        return null;
    }

    /**
     * @param array{working_hours_json?:?string, timezone?:?string} $account
     */
    public static function isOffHours(array $account, \DateTimeImmutable $now): bool
    {
        $tzName = $account['timezone'] ?? 'UTC';
        try {
            $tz = new \DateTimeZone(is_string($tzName) && $tzName !== '' ? $tzName : 'UTC');
        } catch (\Exception) {
            $tz = new \DateTimeZone('UTC');
        }
        $local = $now->setTimezone($tz);

        $hours = $account['working_hours_json'] ?? null;
        if (is_string($hours) && $hours !== '') {
            $decoded = json_decode($hours, true);
            $hours = is_array($decoded) ? $decoded : null;
        }
        if (!is_array($hours) || $hours === []) {
            // No schedule configured — do not invent one. Existing automations
            // would otherwise go dark overnight the moment 034's default
            // respect_off_hours=1 is applied.
            return false;
        }

        $key = strtolower($local->format('D'));
        $window = $hours[$key] ?? null;
        if ($window === null) {
            return true;
        }
        if (!is_array($window) || count($window) < 2) {
            return true;
        }
        $hour = (int) $local->format('G');
        $start = (int) $window[0];
        $end = (int) $window[1];
        return $hour < $start || $hour >= $end;
    }
}
