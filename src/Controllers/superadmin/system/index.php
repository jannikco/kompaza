<?php

/**
 * Superadmin → System Health Dashboard
 *
 * Platform-wide health overview: recent Stripe webhook events, rate limiter
 * activity, database stats, platform integration configuration status, and the
 * tail of the application error log. Read-only — no tenant scoping.
 */

$db = \App\Database\Database::getConnection();

// ---------------------------------------------------------------------------
// Recent webhook events (last 20)
// ---------------------------------------------------------------------------
$webhookEvents = [];
$webhookStats = ['total' => 0, 'last_24h' => 0];
try {
    $stmt = $db->query(
        "SELECT id, stripe_event_id, event_type, processed_at
         FROM webhook_events
         ORDER BY processed_at DESC, id DESC
         LIMIT 20"
    );
    $webhookEvents = $stmt->fetchAll();

    $stmt = $db->query(
        "SELECT COUNT(*) AS total,
                SUM(CASE WHEN processed_at >= (NOW() - INTERVAL 24 HOUR) THEN 1 ELSE 0 END) AS last_24h
         FROM webhook_events"
    );
    $row = $stmt->fetch();
    $webhookStats = [
        'total'    => (int) ($row['total'] ?? 0),
        'last_24h' => (int) ($row['last_24h'] ?? 0),
    ];
} catch (\Throwable $e) {
    // Table missing or query error — leave defaults so the page still renders.
}

// ---------------------------------------------------------------------------
// Rate limiters
// ---------------------------------------------------------------------------
$rateLimits = [];
$rateStats = ['total' => 0, 'active_last_hour' => 0];
try {
    $stmt = $db->query(
        "SELECT COUNT(*) AS total,
                SUM(CASE WHEN last_attempt >= (NOW() - INTERVAL 1 HOUR) THEN 1 ELSE 0 END) AS active_last_hour
         FROM rate_limits"
    );
    $row = $stmt->fetch();
    $rateStats = [
        'total'            => (int) ($row['total'] ?? 0),
        'active_last_hour' => (int) ($row['active_last_hour'] ?? 0),
    ];

    $stmt = $db->query(
        "SELECT identifier, action, attempts, last_attempt
         FROM rate_limits
         ORDER BY last_attempt DESC
         LIMIT 15"
    );
    $rateLimits = $stmt->fetchAll();
} catch (\Throwable $e) {
    // Leave defaults.
}

// ---------------------------------------------------------------------------
// Database stats
// ---------------------------------------------------------------------------
$dbStats = ['table_count' => 0, 'size_mb' => 0, 'version' => 'unknown'];
$biggestTables = [];
try {
    $stmt = $db->query(
        "SELECT COUNT(*) AS table_count,
                ROUND(COALESCE(SUM(data_length + index_length), 0) / 1024 / 1024, 1) AS size_mb,
                VERSION() AS db_version
         FROM information_schema.tables
         WHERE table_schema = DATABASE()"
    );
    $row = $stmt->fetch();
    $dbStats = [
        'table_count' => (int) ($row['table_count'] ?? 0),
        'size_mb'     => (float) ($row['size_mb'] ?? 0),
        'version'     => (string) ($row['db_version'] ?? 'unknown'),
    ];

    $stmt = $db->query(
        "SELECT table_name AS name,
                table_rows AS rows_est,
                ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb
         FROM information_schema.tables
         WHERE table_schema = DATABASE()
         ORDER BY (data_length + index_length) DESC
         LIMIT 5"
    );
    $biggestTables = $stmt->fetchAll();
} catch (\Throwable $e) {
    // Leave defaults.
}

// ---------------------------------------------------------------------------
// Platform integration status
// ---------------------------------------------------------------------------
$emailDefault = (defined('BREVO_API_KEY') && BREVO_API_KEY !== '') ? 'Brevo' : 'Not configured';

$integrations = [
    [
        'name'        => 'Stripe (platform)',
        'configured'  => defined('STRIPE_SECRET_KEY') && STRIPE_SECRET_KEY !== '',
        'detail'      => 'Platform secret key',
    ],
    [
        'name'        => 'Stripe webhook secret',
        'configured'  => defined('STRIPE_WEBHOOK_SECRET') && STRIPE_WEBHOOK_SECRET !== '',
        'detail'      => 'Signature verification',
    ],
    [
        'name'        => 'Email (default)',
        'configured'  => defined('BREVO_API_KEY') && BREVO_API_KEY !== '',
        'detail'      => $emailDefault,
    ],
    [
        'name'        => 'Cron secret',
        'configured'  => defined('CRON_SECRET') && CRON_SECRET !== '',
        'detail'      => 'Scheduled task auth',
    ],
    [
        'name'        => 'Object storage (S3)',
        'configured'  => defined('S3_ACCESS_KEY_ID') && S3_ACCESS_KEY_ID !== ''
                         && defined('S3_SECRET_ACCESS_KEY') && S3_SECRET_ACCESS_KEY !== '',
        'detail'      => defined('S3_BUCKET_NAME') ? (string) S3_BUCKET_NAME : '',
    ],
    [
        'name'        => 'OpenAI',
        'configured'  => defined('OPENAI_API_KEY') && OPENAI_API_KEY !== '',
        'detail'      => 'AI content generation',
    ],
];

// ---------------------------------------------------------------------------
// Error log tail (guarded — file may not exist / not be readable)
// ---------------------------------------------------------------------------
$logLines = [];
$logPath = STORAGE_PATH . '/logs/error.log';
$logExists = is_file($logPath);
$logReadable = $logExists && is_readable($logPath);
$logSize = $logExists ? @filesize($logPath) : 0;
if ($logReadable) {
    $all = @file($logPath, FILE_IGNORE_NEW_LINES);
    if (is_array($all) && !empty($all)) {
        $logLines = array_slice($all, -40);
    }
}

view('superadmin/system/index', [
    'webhookEvents' => $webhookEvents,
    'webhookStats'  => $webhookStats,
    'rateLimits'    => $rateLimits,
    'rateStats'     => $rateStats,
    'dbStats'       => $dbStats,
    'biggestTables' => $biggestTables,
    'integrations'  => $integrations,
    'logLines'      => $logLines,
    'logPath'       => $logPath,
    'logExists'     => $logExists,
    'logReadable'   => $logReadable,
    'logSize'       => $logSize,
]);
