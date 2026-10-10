#!/usr/bin/env php
<?php
/**
 * ConnectPilot Post Automation Runner
 * Run via cron every 5 minutes:
 * */5 * * * * php /var/www/kompaza.com/cron/connectpilot-post-runner.php
 *
 * Polls LinkedIn posts for new comments, matches trigger keywords,
 * auto-likes, auto-replies, auto-connects, DMs, and follow-ups.
 * Every write goes through ActionExecutor.
 */

require_once __DIR__ . '/../src/Config/config.php';

use App\Database\Database;
use App\Models\PostAutomation;
use App\Services\CronLock;
use App\Services\Outreach\PostAutomationProcessor;

$lock = CronLock::acquire('connectpilot-post-runner');
if (!$lock) {
    echo "Post runner is already running; exiting.\n";
    exit(0);
}

$db = Database::getConnection();

$stmt = $db->prepare("
    UPDATE linkedin_accounts
    SET messages_sent_today = 0
    WHERE DATE(last_activity_at) < CURDATE()
");
$stmt->execute();

$automations = PostAutomation::activeWithAccounts();

if (empty($automations)) {
    echo "No active post automations to process.\n";
    exit;
}

$processor = new PostAutomationProcessor();

foreach ($automations as $automation) {
    echo "Processing automation {$automation['id']}: {$automation['name']}\n";
    try {
        $processor->process($automation);
    } catch (\Throwable $e) {
        error_log("PostRunner: Automation {$automation['id']} failed: " . $e->getMessage());
    }
}

echo "Post automation runner completed at " . date('Y-m-d H:i:s') . "\n";
