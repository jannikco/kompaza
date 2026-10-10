<?php

namespace App\Services\Crypto;

/**
 * Authenticated symmetric encryption for secrets at rest.
 *
 * Used for LinkedIn session cookies (`linkedin_accounts.li_at_cookie_enc`), which
 * are a full account takeover for anyone with database read access. Built on
 * libsodium's secretbox (XSalsa20-Poly1305), which ships with PHP 8.2+ — no new
 * Composer dependency, matching this codebase's hand-rolled-integrations style.
 *
 * Envelope format:  v1.<base64url(nonce(24) || ciphertext)>
 *
 * Threat model, stated honestly: the key lives in .env on the same host as the
 * database, so this defeats a stolen database dump (the realistic threat) but not
 * a full server compromise. Losing the key means every connected account must be
 * re-paired — back it up separately from the database.
 */
final class SecretBox
{
    private const VERSION = 'v1';

    /** Cached decoded keys: [primary, ...previous]. */
    private static ?array $keys = null;

    /**
     * True when a usable primary key is configured.
     *
     * Callers MUST check this and fail closed. Never fall back to storing
     * plaintext — a silent downgrade is worse than a visible outage.
     */
    public static function isConfigured(): bool
    {
        try {
            return self::keys() !== [];
        } catch (\Throwable) {
            return false;
        }
    }

    public static function encrypt(string $plaintext): string
    {
        $keys = self::keys();
        if ($keys === []) {
            throw new DecryptionException('OUTREACH_ENCRYPTION_KEY is not configured.');
        }

        $nonce  = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = sodium_crypto_secretbox($plaintext, $nonce, $keys[0]);

        $envelope = self::VERSION . '.' . self::b64UrlEncode($nonce . $cipher);

        sodium_memzero($plaintext);

        return $envelope;
    }

    /**
     * Decrypt an envelope, trying the primary key first and then any previous key
     * so a key rotation does not lock existing rows out.
     *
     * @throws DecryptionException when the envelope is malformed or no key opens it
     */
    public static function decrypt(string $envelope): string
    {
        $keys = self::keys();
        if ($keys === []) {
            throw new DecryptionException('OUTREACH_ENCRYPTION_KEY is not configured.');
        }

        $parts = explode('.', $envelope, 2);
        if (count($parts) !== 2 || $parts[0] !== self::VERSION) {
            throw new DecryptionException('Unrecognised secret envelope format.');
        }

        $raw = self::b64UrlDecode($parts[1]);
        if ($raw === false || strlen($raw) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            throw new DecryptionException('Secret envelope is truncated.');
        }

        $nonce  = substr($raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = substr($raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        foreach ($keys as $key) {
            $plain = sodium_crypto_secretbox_open($cipher, $nonce, $key);
            if ($plain !== false) {
                return $plain;
            }
        }

        throw new DecryptionException('Secret could not be decrypted with any configured key.');
    }

    /** Generate a fresh base64 key. Used by the setup docs and tests. */
    public static function generateKey(): string
    {
        return base64_encode(sodium_crypto_secretbox_keygen());
    }

    /**
     * Decoded keys, primary first.
     *
     * @return list<string> empty when nothing usable is configured
     */
    private static function keys(): array
    {
        // Only a successful lookup is memoized. Caching an empty result would mean
        // that a single call made before config.php defines the constants disables
        // encryption for the rest of the request.
        if (self::$keys) {
            return self::$keys;
        }

        $keys = [];
        foreach (['OUTREACH_ENCRYPTION_KEY', 'OUTREACH_ENCRYPTION_KEY_PREVIOUS'] as $const) {
            $raw = defined($const) ? (string) constant($const) : '';
            if ($raw === '') {
                continue;
            }
            $decoded = base64_decode($raw, true);
            if ($decoded === false || strlen($decoded) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
                error_log("SecretBox: {$const} is not 32 base64-encoded bytes; ignoring it.");
                continue;
            }
            $keys[] = $decoded;
        }

        return self::$keys = $keys;
    }

    private static function b64UrlEncode(string $bin): string
    {
        return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
    }

    private static function b64UrlDecode(string $text): string|false
    {
        return base64_decode(strtr($text, '-_', '+/'), true);
    }
}
