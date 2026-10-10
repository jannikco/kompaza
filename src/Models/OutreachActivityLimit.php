<?php

namespace App\Models;

use App\Database\Database;

class OutreachActivityLimit
{
    public const CHANNELS = [
        'dm'                   => ['period' => 'day',  'default' => 50, 'cooldown' => 8,  'readonly' => 0],
        'connected_reply'      => ['period' => 'day',  'default' => 50, 'cooldown' => 5,  'readonly' => 0],
        'non_connected_reply'  => ['period' => 'day',  'default' => 30, 'cooldown' => 8,  'readonly' => 0],
        'connection_daily'     => ['period' => 'day',  'default' => 20, 'cooldown' => 15, 'readonly' => 0],
        'connection_weekly'    => ['period' => 'week', 'default' => 80, 'cooldown' => 15, 'readonly' => 0],
        'comment'              => ['period' => 'day',  'default' => 40, 'cooldown' => 5,  'readonly' => 0],
        'reaction'             => ['period' => 'day',  'default' => 80, 'cooldown' => 3,  'readonly' => 0],
        'profile_view'         => ['period' => 'day',  'default' => 80, 'cooldown' => 4,  'readonly' => 0],
        'enrich_daily'         => ['period' => 'day',  'default' => 80, 'cooldown' => 2,  'readonly' => 0],
        'inmail'               => ['period' => 'day',  'default' => 0,  'cooldown' => 0,  'readonly' => 1],
    ];

    public static function forChannel(int $accountId, string $channel): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM outreach_activity_limits
             WHERE linkedin_account_id = ? AND channel = ?
             LIMIT 1
        ");
        $stmt->execute([$accountId, $channel]);
        $row = $stmt->fetch() ?: null;
        if ($row) {
            self::rollWindowIfNeeded($row);
        }
        return $row ?: null;
    }

    /**
     * Insert missing channel rows for an account. Safe to call on every connect.
     */
    public static function seedForAccount(int $tenantId, int $accountId, array $account = []): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT IGNORE INTO outreach_activity_limits
                (tenant_id, linkedin_account_id, channel, period, limit_value, used, window_start,
                 is_readonly, is_paused, approval_mode, cooldown_seconds)
            VALUES (?, ?, ?, ?, ?, 0, CURDATE(), ?, 0, 'auto', ?)
        ");

        $dm = isset($account['daily_message_limit']) ? (int) $account['daily_message_limit'] : null;
        $cn = isset($account['daily_connection_limit']) ? (int) $account['daily_connection_limit'] : null;

        foreach (self::CHANNELS as $channel => $meta) {
            $cap = $meta['default'];
            if ($channel === 'dm' && $dm !== null) {
                $cap = $dm;
            }
            if ($channel === 'connection_daily' && $cn !== null) {
                $cap = $cn;
            }
            $stmt->execute([
                $tenantId,
                $accountId,
                $channel,
                $meta['period'],
                $cap,
                $meta['readonly'],
                $meta['cooldown'],
            ]);
        }
    }

    public static function spend(int $accountId, string $channel, int $cooldownSeconds = 0): void
    {
        $row = self::forChannel($accountId, $channel);
        if (!$row) {
            return;
        }

        $db = Database::getConnection();
        $next = $cooldownSeconds > 0
            ? date('Y-m-d H:i:s', time() + $cooldownSeconds)
            : ($row['next_action_at'] ?? null);

        $stmt = $db->prepare("
            UPDATE outreach_activity_limits
               SET used = used + 1,
                   last_action_at = NOW(),
                   next_action_at = ?
             WHERE id = ?
        ");
        $stmt->execute([$next, $row['id']]);
    }

    public static function setLimit(int $accountId, string $channel, int $value): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE outreach_activity_limits
               SET limit_value = ?, is_paused = 0
             WHERE linkedin_account_id = ? AND channel = ?
        ");
        $stmt->execute([$value, $accountId, $channel]);
    }

    private static function rollWindowIfNeeded(array &$row): void
    {
        $start = $row['window_start'] ?? null;
        if (!$start) {
            return;
        }
        $period = $row['period'] ?? 'day';
        $expired = $period === 'week'
            ? strtotime($start) <= strtotime('-7 days')
            : (substr((string) $start, 0, 10) < date('Y-m-d'));

        if (!$expired) {
            return;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE outreach_activity_limits
               SET used = 0, window_start = CURDATE()
             WHERE id = ?
        ");
        $stmt->execute([$row['id']]);
        $row['used'] = 0;
        $row['window_start'] = date('Y-m-d');
    }
}
