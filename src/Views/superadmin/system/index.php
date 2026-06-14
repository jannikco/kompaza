<?php
$pageTitle = 'System Health';
$currentPage = 'system';
ob_start();

/** Format a byte count to a human-readable string. */
$fmtBytes = static function ($bytes): string {
    $bytes = (float) $bytes;
    if ($bytes <= 0) {
        return '0 B';
    }
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = (int) floor(log($bytes, 1024));
    $i = max(0, min($i, count($units) - 1));
    return round($bytes / (1024 ** $i), $i ? 1 : 0) . ' ' . $units[$i];
};
?>

<!-- Top stat cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-gray-400">Webhook Events (24h)</p>
            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <p class="mt-2 text-3xl font-bold text-white"><?= number_format($webhookStats['last_24h']) ?></p>
        <p class="mt-1 text-xs text-gray-500"><?= number_format($webhookStats['total']) ?> total recorded</p>
    </div>

    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-gray-400">Active Rate Limiters</p>
            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12a9 9 0 1018 0 9 9 0 00-18 0zm9-4v4l3 2"/></svg>
        </div>
        <p class="mt-2 text-3xl font-bold text-white"><?= number_format($rateStats['active_last_hour']) ?></p>
        <p class="mt-1 text-xs text-gray-500"><?= number_format($rateStats['total']) ?> total limiter rows</p>
    </div>

    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-gray-400">Database Tables</p>
            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 4 3 8 3s8-1 8-3V7M4 7c0 2 4 3 8 3s8-1 8-3M4 7c0-2 4-3 8-3s8 1 8 3"/></svg>
        </div>
        <p class="mt-2 text-3xl font-bold text-white"><?= number_format($dbStats['table_count']) ?></p>
        <p class="mt-1 text-xs text-gray-500"><?= h((string) $dbStats['size_mb']) ?> MB on disk</p>
    </div>

    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-gray-400">Database Engine</p>
            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
        </div>
        <p class="mt-2 text-xl font-bold text-white break-all"><?= h($dbStats['version']) ?></p>
        <p class="mt-1 text-xs text-gray-500">MySQL / MariaDB</p>
    </div>
</div>

<!-- Integration status -->
<div class="bg-gray-800 rounded-xl border border-gray-700 mb-6">
    <div class="px-6 py-4 border-b border-gray-700">
        <h3 class="text-lg font-semibold text-white">Platform Integrations</h3>
        <p class="text-xs text-gray-400 mt-0.5">Configuration status of platform-level credentials.</p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
        <?php foreach ($integrations as $integration): ?>
        <div class="flex items-start justify-between bg-gray-900/50 border border-gray-700 rounded-lg p-4">
            <div class="min-w-0">
                <p class="text-sm font-medium text-white"><?= h($integration['name']) ?></p>
                <p class="text-xs text-gray-400 mt-0.5 truncate"><?= h($integration['detail']) ?></p>
            </div>
            <?php if (!empty($integration['configured'])): ?>
            <span class="inline-flex items-center shrink-0 ml-3 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-300">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                Configured
            </span>
            <?php else: ?>
            <span class="inline-flex items-center shrink-0 ml-3 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-900 text-red-300">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.7 7.3a1 1 0 00-1.4 1.4L8.6 10l-1.3 1.3a1 1 0 101.4 1.4L10 11.4l1.3 1.3a1 1 0 001.4-1.4L11.4 10l1.3-1.3a1 1 0 00-1.4-1.4L10 8.6 8.7 7.3z" clip-rule="evenodd"/></svg>
                Missing
            </span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Recent webhook events -->
    <div class="bg-gray-800 rounded-xl border border-gray-700">
        <div class="px-6 py-4 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">Recent Webhook Events</h3>
            <p class="text-xs text-gray-400 mt-0.5">Last 20 processed Stripe events.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Event ID</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Processed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php if (empty($webhookEvents)): ?>
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">No webhook events recorded yet.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($webhookEvents as $ev): ?>
                    <tr class="hover:bg-gray-700/50">
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-900/60 text-indigo-300">
                                <?= h($ev['event_type'] ?? '—') ?>
                            </span>
                        </td>
                        <td class="px-6 py-3 text-xs text-gray-400 font-mono break-all"><?= h($ev['stripe_event_id'] ?? '') ?></td>
                        <td class="px-6 py-3 text-sm text-gray-400 whitespace-nowrap"><?= h(formatDate($ev['processed_at'] ?? '', 'd M Y H:i')) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent rate limiters -->
    <div class="bg-gray-800 rounded-xl border border-gray-700">
        <div class="px-6 py-4 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">Recent Rate Limiters</h3>
            <p class="text-xs text-gray-400 mt-0.5">Most recently triggered limiters.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Identifier</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Action</th>
                        <th class="text-right px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Attempts</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Last</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php if (empty($rateLimits)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">No active rate limiters.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($rateLimits as $rl): ?>
                    <tr class="hover:bg-gray-700/50">
                        <td class="px-6 py-3 text-xs text-gray-300 font-mono break-all"><?= h($rl['identifier'] ?? '') ?></td>
                        <td class="px-6 py-3 text-sm text-gray-300"><?= h($rl['action'] ?? '') ?></td>
                        <td class="px-6 py-3 text-right">
                            <?php $attempts = (int) ($rl['attempts'] ?? 0); ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?= $attempts >= 5 ? 'bg-red-900 text-red-300' : 'bg-gray-700 text-gray-300' ?>">
                                <?= number_format($attempts) ?>
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-400 whitespace-nowrap"><?= h(formatDate($rl['last_attempt'] ?? '', 'd M Y H:i')) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Largest tables -->
<div class="bg-gray-800 rounded-xl border border-gray-700 mb-6">
    <div class="px-6 py-4 border-b border-gray-700">
        <h3 class="text-lg font-semibold text-white">Largest Tables</h3>
        <p class="text-xs text-gray-400 mt-0.5">By data + index size.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Table</th>
                    <th class="text-right px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Rows (est.)</th>
                    <th class="text-right px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Size</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <?php if (empty($biggestTables)): ?>
                <tr>
                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">No table data available.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($biggestTables as $tbl): ?>
                <tr class="hover:bg-gray-700/50">
                    <td class="px-6 py-3 text-sm text-gray-200 font-mono"><?= h($tbl['name'] ?? '') ?></td>
                    <td class="px-6 py-3 text-right text-sm text-gray-400"><?= number_format((int) ($tbl['rows_est'] ?? 0)) ?></td>
                    <td class="px-6 py-3 text-right text-sm text-gray-400"><?= h((string) ($tbl['size_mb'] ?? 0)) ?> MB</td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Error log tail -->
<div class="bg-gray-800 rounded-xl border border-gray-700">
    <div class="px-6 py-4 border-b border-gray-700 flex items-center justify-between flex-wrap gap-2">
        <div>
            <h3 class="text-lg font-semibold text-white">Error Log</h3>
            <p class="text-xs text-gray-400 mt-0.5 font-mono break-all"><?= h($logPath) ?></p>
        </div>
        <?php if ($logExists): ?>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-300">
            <?= h($fmtBytes($logSize)) ?> · last 40 lines
        </span>
        <?php endif; ?>
    </div>
    <div class="p-6">
        <?php if (!$logExists): ?>
            <div class="text-sm text-gray-500 bg-gray-900/50 border border-gray-700 rounded-lg p-4">
                Log file does not exist yet.
            </div>
        <?php elseif (!$logReadable): ?>
            <div class="text-sm text-yellow-300 bg-yellow-900/30 border border-yellow-700 rounded-lg p-4">
                Log file exists but is not readable.
            </div>
        <?php elseif (empty($logLines)): ?>
            <div class="text-sm text-gray-500 bg-gray-900/50 border border-gray-700 rounded-lg p-4">
                Log file is empty.
            </div>
        <?php else: ?>
            <pre class="bg-gray-900 border border-gray-700 rounded-lg p-4 overflow-x-auto text-xs text-gray-300 leading-relaxed whitespace-pre-wrap break-words max-h-96"><?php foreach ($logLines as $line) {
                echo h($line) . "\n";
            } ?></pre>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/superadmin/layouts/layout.php';
?>
