#!/usr/bin/env php
<?php
/**
 * Encrypt LinkedIn session cookies at rest.
 *
 * `linkedin_accounts.li_at_cookie` and `.csrf_token` were stored in plaintext,
 * which makes any database read a full LinkedIn account takeover. This moves them
 * into sodium secretbox envelopes in `li_at_cookie_enc` / `csrf_token_enc` and
 * sets `encryption_version = 1`.
 *
 * Idempotent — safe to re-run. Every row is verified by decrypting it back and
 * comparing before the plaintext is cleared.
 *
 * Usage:
 *   php scripts/outreach-encrypt-cookies.php --dry-run     # report only, change nothing
 *   php scripts/outreach-encrypt-cookies.php               # migrate plaintext -> encrypted
 *   php scripts/outreach-encrypt-cookies.php --rotate      # re-encrypt under the new primary key
 *
 * Run this BEFORE database/migrations/046_outreach_drop_plaintext_cookies.sql, and only
 * apply 046 once this reports "0 rows still unencrypted".
 */

require_once __DIR__ . '/../src/Config/config.php';

use App\Database\Database;
use App\Models\LinkedInAccount;
use App\Services\Crypto\SecretBox;

$args    = array_slice($argv, 1);
$dryRun  = in_array('--dry-run', $args, true);
$rotate  = in_array('--rotate', $args, true);

if (!SecretBox::isConfigured()) {
    fwrite(STDERR, "OUTREACH_ENCRYPTION_KEY is not set (or is not 32 base64-encoded bytes).\n");
    fwrite(STDERR, "Generate one with:\n");
    fwrite(STDERR, "  php -r \"echo base64_encode(sodium_crypto_secretbox_keygen()), PHP_EOL;\"\n");
    exit(1);
}

$db = Database::getConnection();

// Confirm the target columns exist, so this fails loudly rather than half-migrating.
$cols = $db->query("SHOW COLUMNS FROM linkedin_accounts")->fetchAll(PDO::FETCH_COLUMN);
foreach (['li_at_cookie_enc', 'csrf_token_enc', 'encryption_version'] as $needed) {
    if (!in_array($needed, $cols, true)) {
        fwrite(STDERR, "Column linkedin_accounts.{$needed} is missing.\n");
        fwrite(STDERR, "Apply database/migrations/034_outreach_schema_fixes.sql first.\n");
        exit(1);
    }
}
$hasPlaintextColumns = in_array('li_at_cookie', $cols, true);

echo $dryRun ? "DRY RUN — no changes will be written.\n" : "Encrypting LinkedIn sessions...\n";

$migrated = $failed = $skipped = 0;

if ($rotate) {
    // Re-encrypt already-encrypted rows so they use the current primary key.
    $rows = $db->query("
        SELECT id, tenant_id, li_at_cookie_enc, csrf_token_enc
          FROM linkedin_accounts
         WHERE encryption_version >= 1 AND li_at_cookie_enc IS NOT NULL
    ")->fetchAll();

    foreach ($rows as $row) {
        try {
            $liAt = SecretBox::decrypt((string) $row['li_at_cookie_enc']);
            $jsid = SecretBox::decrypt((string) $row['csrf_token_enc']);
        } catch (Throwable $e) {
            fwrite(STDERR, "  !! account {$row['id']}: cannot decrypt ({$e->getMessage()})\n");
            $failed++;
            continue;
        }

        if ($dryRun) {
            echo "  would re-encrypt account {$row['id']} (tenant {$row['tenant_id']})\n";
            $migrated++;
            continue;
        }

        $stmt = $db->prepare("
            UPDATE linkedin_accounts
               SET li_at_cookie_enc = ?, csrf_token_enc = ?, encryption_version = ?
             WHERE id = ?
        ");
        $stmt->execute([
            SecretBox::encrypt($liAt),
            SecretBox::encrypt($jsid),
            LinkedInAccount::ENCRYPTION_VERSION,
            $row['id'],
        ]);
        echo "  re-encrypted account {$row['id']}\n";
        $migrated++;
    }
} elseif (!$hasPlaintextColumns) {
    echo "  Plaintext columns already dropped (migration 046 applied). Nothing to do.\n";
} else {
    $rows = $db->query("
        SELECT id, tenant_id, li_at_cookie, csrf_token
          FROM linkedin_accounts
         WHERE encryption_version = 0
           AND li_at_cookie IS NOT NULL
           AND li_at_cookie <> ''
    ")->fetchAll();

    foreach ($rows as $row) {
        $liAt = (string) $row['li_at_cookie'];
        $jsid = (string) ($row['csrf_token'] ?? '');

        if ($dryRun) {
            echo "  would encrypt account {$row['id']} (tenant {$row['tenant_id']})\n";
            $migrated++;
            continue;
        }

        try {
            $encLiAt = SecretBox::encrypt($liAt);
            $encJsid = SecretBox::encrypt($jsid);

            // Verify before destroying the plaintext.
            if (SecretBox::decrypt($encLiAt) !== $liAt || SecretBox::decrypt($encJsid) !== $jsid) {
                throw new RuntimeException('round-trip verification failed');
            }

            $stmt = $db->prepare("
                UPDATE linkedin_accounts
                   SET li_at_cookie_enc = ?, csrf_token_enc = ?, encryption_version = ?,
                       li_at_cookie = NULL, csrf_token = NULL
                 WHERE id = ?
            ");
            $stmt->execute([$encLiAt, $encJsid, LinkedInAccount::ENCRYPTION_VERSION, $row['id']]);

            echo "  encrypted account {$row['id']} (tenant {$row['tenant_id']})\n";
            $migrated++;
        } catch (Throwable $e) {
            fwrite(STDERR, "  !! account {$row['id']}: {$e->getMessage()}\n");
            $failed++;
        }
    }
}

// Rows that still hold plaintext. Migration 046 must not run while this is > 0.
$remaining = 0;
if ($hasPlaintextColumns) {
    $remaining = (int) $db->query("
        SELECT COUNT(*) FROM linkedin_accounts
         WHERE encryption_version = 0 AND li_at_cookie IS NOT NULL AND li_at_cookie <> ''
    ")->fetchColumn();
}

echo "\n";
echo "  processed: {$migrated}\n";
echo "  failed:    {$failed}\n";
echo "  {$remaining} rows still unencrypted\n";

if ($failed > 0) {
    fwrite(STDERR, "\nSome rows failed. Do NOT apply migration 046 until this reports 0 failures.\n");
    exit(1);
}
if ($remaining === 0 && !$dryRun) {
    echo "\nAll sessions encrypted. It is now safe to apply:\n";
    echo "  mysql kompaza < database/migrations/046_outreach_drop_plaintext_cookies.sql\n";
}

exit(0);
