#!/usr/bin/env php
<?php

/**
 * Cleanup Unverified Accounts
 *
 * Deletes tenant accounts where the owner never verified their email
 * within 24 hours of registration.
 *
 * Cron: 0 * * * * php /var/www/kompaza.com/bin/cleanup-unverified.php
 */

require_once __DIR__ . '/../src/Config/config.php';

use App\Database\Database;

$db = Database::getConnection();

// Find unverified tenant_admin users older than 24 hours
$stmt = $db->prepare("
    SELECT u.id AS user_id, u.tenant_id, u.email, t.slug
    FROM users u
    LEFT JOIN tenants t ON t.id = u.tenant_id
    WHERE u.role = 'tenant_admin'
      AND u.email_verified_at IS NULL
      AND u.created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
");
$stmt->execute();
$unverified = $stmt->fetchAll();

if (empty($unverified)) {
    echo date('Y-m-d H:i:s') . " - No unverified accounts to clean up.\n";
    exit(0);
}

echo date('Y-m-d H:i:s') . " - Found " . count($unverified) . " unverified account(s) to clean up.\n";

foreach ($unverified as $row) {
    $userId = $row['user_id'];
    $tenantId = $row['tenant_id'];
    $email = $row['email'];
    $slug = $row['slug'] ?? 'unknown';

    try {
        $db->beginTransaction();

        // Delete verification tokens
        $stmt = $db->prepare("DELETE FROM email_verification_tokens WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Delete all users belonging to this tenant
        if ($tenantId) {
            $stmt = $db->prepare("DELETE FROM users WHERE tenant_id = ?");
            $stmt->execute([$tenantId]);

            // Delete the tenant
            $stmt = $db->prepare("DELETE FROM tenants WHERE id = ?");
            $stmt->execute([$tenantId]);
        } else {
            // Orphan user without tenant
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
        }

        $db->commit();
        echo "  Deleted: {$email} (tenant: {$slug}, id: {$tenantId})\n";

    } catch (\Exception $e) {
        $db->rollBack();
        echo "  ERROR deleting {$email}: " . $e->getMessage() . "\n";
        error_log("cleanup-unverified: Failed to delete {$email}: " . $e->getMessage());
    }
}

echo date('Y-m-d H:i:s') . " - Cleanup complete.\n";
