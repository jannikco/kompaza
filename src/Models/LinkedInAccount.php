<?php

namespace App\Models;

use App\Database\Database;
use App\Services\Crypto\DecryptionException;
use App\Services\Crypto\SecretBox;

/**
 * The connected LinkedIn session for a tenant.
 *
 * This is the ONLY place LinkedIn cookie material is read or written. Everything
 * else — controllers, views, logs, API responses — must go through
 * `toPublicArray()`, which cannot return cookie values.
 *
 * Rows carry `encryption_version`:
 *   0 = legacy plaintext in li_at_cookie / csrf_token (pre-migration rows)
 *   1 = sodium secretbox envelopes in li_at_cookie_enc / csrf_token_enc
 * `session()` reads both so the transition needs no downtime;
 * `scripts/outreach-encrypt-cookies.php` migrates 0 → 1, and migration 046 drops the
 * plaintext columns once no version-0 rows remain.
 */
class LinkedInAccount
{
    public const ENCRYPTION_VERSION = 1;

    /** Cookie-bearing columns that must never reach a view, log or API response. */
    private const SECRET_COLUMNS = [
        'li_at_cookie', 'csrf_token', 'li_at_cookie_enc', 'csrf_token_enc',
    ];

    /** Columns `update()` may write. */
    private const COLUMNS = [
        'linkedin_name', 'linkedin_email', 'linkedin_profile_url', 'avatar_url',
        'member_urn', 'public_identifier', 'profile_urn',
        'cookie_expires_at', 'status', 'daily_connection_limit', 'daily_message_limit',
        'connections_sent_today', 'messages_sent_today', 'last_activity_at',
        'user_agent', 'accept_language', 'li_track_json', 'sec_ch_ua',
        'sec_ch_ua_platform', 'timezone', 'fingerprint_updated_at',
        'session_source', 'session_synced_at', 'last_auth_failure_at',
        'auth_failure_count', 'read_mode', 'write_mode', 'allow_server_write_fallback',
        'safety_mode', 'working_hours_json', 'respect_off_hours',
        'emergency_stop', 'emergency_stopped_at', 'emergency_stopped_by',
        'inmail_credits', 'inmail_credits_checked_at', 'enrich_used_today',
    ];

    /** Consecutive auth failures tolerated before the session is marked expired. */
    private const AUTH_FAILURE_THRESHOLD = 2;

    public static function find(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM linkedin_accounts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** The tenant's connected account. One per tenant today. */
    public static function forTenant(int $tenantId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM linkedin_accounts WHERE tenant_id = ? ORDER BY id ASC LIMIT 1");
        $stmt->execute([$tenantId]);
        return $stmt->fetch() ?: null;
    }

    public static function allByTenant(int $tenantId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM linkedin_accounts WHERE tenant_id = ? ORDER BY id ASC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    /**
     * Decrypted session credentials for an account.
     *
     * @return array{li_at: string, jsessionid: string}
     * @throws DecryptionException when the account has no usable session
     */
    public static function session(int $accountId): array
    {
        $row = self::find($accountId);
        if (!$row) {
            throw new DecryptionException("LinkedIn account {$accountId} not found.");
        }

        if ((int) $row['encryption_version'] >= 1) {
            if (empty($row['li_at_cookie_enc'])) {
                throw new DecryptionException("LinkedIn account {$accountId} has no stored session.");
            }
            return [
                'li_at'      => SecretBox::decrypt((string) $row['li_at_cookie_enc']),
                'jsessionid' => trim(SecretBox::decrypt((string) $row['csrf_token_enc']), '"'),
            ];
        }

        // Legacy plaintext row, not yet migrated.
        if (empty($row['li_at_cookie'])) {
            throw new DecryptionException("LinkedIn account {$accountId} has no stored session.");
        }
        return [
            'li_at'      => (string) $row['li_at_cookie'],
            'jsessionid' => trim((string) $row['csrf_token'], '"'),
        ];
    }

    public static function hasSession(int $accountId): bool
    {
        try {
            self::session($accountId);
            return true;
        } catch (DecryptionException) {
            return false;
        }
    }

    /**
     * Persist a session, encrypted. Called by the extension sync endpoint and by
     * the manual cookie-paste form.
     *
     * @param int|null $expiresAt Unix timestamp from the browser cookie, when known.
     *                            This is the only writer of `cookie_expires_at`.
     */
    public static function storeSession(
        int $accountId,
        string $liAt,
        string $jsessionid,
        ?int $expiresAt = null,
        string $source = 'manual'
    ): void {
        if (!SecretBox::isConfigured()) {
            // Fail closed. Storing plaintext because the key is missing would
            // silently reintroduce the vulnerability this exists to remove.
            throw new DecryptionException(
                'OUTREACH_ENCRYPTION_KEY is not configured; refusing to store a LinkedIn session.'
            );
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE linkedin_accounts
               SET li_at_cookie_enc   = ?,
                   csrf_token_enc     = ?,
                   encryption_version = ?,
                   li_at_cookie       = NULL,
                   csrf_token         = NULL,
                   cookie_expires_at  = COALESCE(?, cookie_expires_at),
                   session_source     = ?,
                   session_synced_at  = NOW(),
                   status             = 'active',
                   auth_failure_count = 0,
                   last_auth_failure_at = NULL
             WHERE id = ?
        ");
        $stmt->execute([
            SecretBox::encrypt($liAt),
            SecretBox::encrypt(trim($jsessionid, '"')),
            self::ENCRYPTION_VERSION,
            $expiresAt !== null ? date('Y-m-d H:i:s', $expiresAt) : null,
            $source,
            $accountId,
        ]);
    }

    /**
     * Store the browser fingerprint mirrored by the extension.
     *
     * This replaces the hardcoded User-Agent and x-li-track header in
     * LinkedInService, which is pinned to a clientVersion that will rot. Values
     * here describe the user's real browser, so server-side calls match it.
     */
    public static function storeFingerprint(int $accountId, array $fp): void
    {
        $liTrack = $fp['li_track'] ?? null;
        if (is_array($liTrack)) {
            $liTrack = json_encode($liTrack, JSON_UNESCAPED_SLASHES);
        }

        self::update($accountId, array_filter([
            'user_agent'         => $fp['user_agent'] ?? null,
            'accept_language'    => $fp['accept_language'] ?? null,
            'li_track_json'      => $liTrack,
            'sec_ch_ua'          => $fp['sec_ch_ua'] ?? null,
            'sec_ch_ua_platform' => $fp['sec_ch_ua_platform'] ?? null,
            'timezone'           => $fp['timezone'] ?? null,
            'member_urn'         => $fp['member_urn'] ?? null,
            'public_identifier'  => $fp['public_identifier'] ?? null,
            'profile_urn'        => $fp['profile_urn'] ?? null,
        ], static fn ($v) => $v !== null && $v !== '') + [
            'fingerprint_updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            if (!in_array($key, self::COLUMNS, true)) {
                continue;
            }
            $fields[] = "`$key` = ?";
            $values[] = $value;
        }
        if (empty($fields)) {
            return false;
        }

        $db = Database::getConnection();
        $values[] = $id;
        $stmt = $db->prepare("UPDATE linkedin_accounts SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function markActive(int $id): void
    {
        self::update($id, [
            'status'               => 'active',
            'auth_failure_count'   => 0,
            'last_auth_failure_at' => null,
            'last_activity_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Record an authentication failure.
     *
     * LinkedIn returns spurious 401/403s under load, so one failure is tolerated;
     * the second consecutive one marks the session expired. Nothing in the
     * original codebase ever set this status, so an expired cookie simply produced
     * silent failures forever.
     *
     * @return bool true when this failure tipped the account into cookie_expired
     */
    public static function markAuthFailure(int $id, string $reason = ''): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE linkedin_accounts
               SET auth_failure_count = auth_failure_count + 1,
                   last_auth_failure_at = NOW()
             WHERE id = ?
        ");
        $stmt->execute([$id]);

        $stmt = $db->prepare("SELECT auth_failure_count FROM linkedin_accounts WHERE id = ?");
        $stmt->execute([$id]);
        $count = (int) ($stmt->fetchColumn() ?: 0);

        if ($count >= self::AUTH_FAILURE_THRESHOLD) {
            self::markCookieExpired($id, $reason ?: 'repeated authentication failures');
            return true;
        }
        return false;
    }

    public static function markCookieExpired(int $id, string $reason = ''): void
    {
        self::update($id, ['status' => 'cookie_expired']);
        if ($reason !== '') {
            error_log("LinkedInAccount {$id}: session marked expired ({$reason}).");
        }
    }

    /** True when the stored cookie is known to expire within the given window. */
    public static function expiresWithin(array $row, int $seconds = 172800): bool
    {
        if (empty($row['cookie_expires_at'])) {
            return false;
        }
        return strtotime($row['cookie_expires_at']) <= time() + $seconds;
    }

    /**
     * Strip every secret from a row before it leaves the model layer.
     *
     * Use this for anything rendered, logged, or serialised. The admin account
     * view used to echo the raw li_at back into the HTML — that is exactly what
     * this prevents.
     */
    public static function toPublicArray(array $row): array
    {
        foreach (self::SECRET_COLUMNS as $col) {
            unset($row[$col]);
        }
        $row['has_session']        = !empty($row['encryption_version']) || !empty($row['session_synced_at']);
        $row['session_encrypted']  = (int) ($row['encryption_version'] ?? 0) >= 1;
        return $row;
    }
}
