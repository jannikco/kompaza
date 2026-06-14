<?php
$pageTitle = 'Audit Log';
$currentPage = 'audit';

/**
 * Build a query string for pagination links that preserves the active filters.
 */
$buildQuery = function (int $targetPage) use ($filters): string {
    $qs = array_filter([
        'action'    => $filters['action'],
        'tenant_id' => $filters['tenant_id'],
        'user_id'   => $filters['user_id'],
        'date_from' => $filters['date_from'],
        'date_to'   => $filters['date_to'],
    ], static fn ($v) => $v !== '');
    $qs['page'] = $targetPage;
    return '/audit?' . http_build_query($qs);
};

$hasFilters = $filters['action'] !== '' || $filters['tenant_id'] !== '' || $filters['user_id'] !== ''
    || $filters['date_from'] !== '' || $filters['date_to'] !== '';

ob_start();
?>

<!-- Filter form -->
<div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-6">
    <form method="GET" action="/audit" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
        <!-- Action -->
        <div class="lg:col-span-2">
            <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Action</label>
            <input type="text" name="action" value="<?= h($filters['action']) ?>" placeholder="e.g. create, login, delete"
                class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white placeholder-gray-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>

        <!-- Tenant -->
        <div>
            <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Tenant</label>
            <select name="tenant_id"
                class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">All tenants</option>
                <?php foreach ($tenants as $t): ?>
                <option value="<?= (int)$t['id'] ?>" <?= (string)$filters['tenant_id'] === (string)$t['id'] ? 'selected' : '' ?>>
                    <?= h($t['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- User ID -->
        <div>
            <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">User ID</label>
            <input type="number" name="user_id" min="1" value="<?= h($filters['user_id']) ?>" placeholder="Any"
                class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white placeholder-gray-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>

        <!-- Date from -->
        <div>
            <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">From</label>
            <input type="date" name="date_from" value="<?= h($filters['date_from']) ?>"
                class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent [color-scheme:dark]">
        </div>

        <!-- Date to -->
        <div>
            <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">To</label>
            <input type="date" name="date_to" value="<?= h($filters['date_to']) ?>"
                class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent [color-scheme:dark]">
        </div>

        <!-- Actions -->
        <div class="sm:col-span-2 lg:col-span-6 flex items-center gap-3">
            <button type="submit"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                Apply Filters
            </button>
            <?php if ($hasFilters): ?>
            <a href="/audit" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-200 text-sm font-medium rounded-lg transition">
                Clear
            </a>
            <?php endif; ?>
            <span class="ml-auto text-sm text-gray-400">
                <?= number_format($totalRows) ?> <?= $totalRows === 1 ? 'entry' : 'entries' ?>
            </span>
        </div>
    </form>
</div>

<!-- Audit log table -->
<div class="bg-gray-800 rounded-xl border border-gray-700">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Timestamp</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Actor</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Tenant</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Action</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Entity</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                        <?= $hasFilters ? 'No audit entries match the current filters.' : 'No audit entries recorded yet.' ?>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($logs as $log): ?>
                <tr class="hover:bg-gray-700/50 align-top">
                    <!-- Timestamp -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-200"><?= h(formatDate($log['created_at'], 'd M Y')) ?></div>
                        <div class="text-xs text-gray-500"><?= h(formatDate($log['created_at'], 'H:i:s')) ?></div>
                    </td>

                    <!-- Actor -->
                    <td class="px-6 py-4">
                        <?php if (!empty($log['actor_name']) || !empty($log['actor_email'])): ?>
                        <div class="text-sm font-medium text-white"><?= h($log['actor_name'] ?? '—') ?></div>
                        <div class="text-xs text-gray-400"><?= h($log['actor_email'] ?? '') ?></div>
                        <?php if (!empty($log['actor_role'])): ?>
                        <div class="text-[10px] text-gray-500 uppercase tracking-wide mt-0.5"><?= h(str_replace('_', ' ', $log['actor_role'])) ?></div>
                        <?php endif; ?>
                        <?php elseif (!empty($log['user_id'])): ?>
                        <div class="text-sm text-gray-400">User #<?= (int)$log['user_id'] ?></div>
                        <div class="text-xs text-gray-600">deleted</div>
                        <?php else: ?>
                        <span class="text-sm text-gray-500">System</span>
                        <?php endif; ?>
                    </td>

                    <!-- Tenant -->
                    <td class="px-6 py-4">
                        <?php if (!empty($log['tenant_name'])): ?>
                        <div class="text-sm text-gray-200"><?= h($log['tenant_name']) ?></div>
                        <?php if (!empty($log['tenant_slug'])): ?>
                        <div class="text-xs text-gray-500"><?= h($log['tenant_slug']) ?></div>
                        <?php endif; ?>
                        <?php elseif (!empty($log['tenant_id'])): ?>
                        <div class="text-sm text-gray-400">#<?= (int)$log['tenant_id'] ?></div>
                        <div class="text-xs text-gray-600">deleted</div>
                        <?php else: ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-900/60 text-indigo-300">Platform</span>
                        <?php endif; ?>
                    </td>

                    <!-- Action -->
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-200 font-mono">
                            <?= h($log['action']) ?>
                        </span>
                    </td>

                    <!-- Entity -->
                    <td class="px-6 py-4">
                        <?php if (!empty($log['entity_type'])): ?>
                        <div class="text-sm text-gray-300"><?= h($log['entity_type']) ?></div>
                        <?php if (!empty($log['entity_id'])): ?>
                        <div class="text-xs text-gray-500">#<?= (int)$log['entity_id'] ?></div>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="text-sm text-gray-600">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- IP address -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-xs text-gray-400 font-mono"><?= h($log['ip_address'] ?? '—') ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalRows > 0): ?>
    <div class="px-6 py-4 border-t border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="text-sm text-gray-400">
            Showing <span class="text-gray-200 font-medium"><?= number_format($rangeStart) ?></span>–<span class="text-gray-200 font-medium"><?= number_format($rangeEnd) ?></span>
            of <span class="text-gray-200 font-medium"><?= number_format($totalRows) ?></span>
        </div>
        <div class="flex items-center gap-2">
            <?php if ($page > 1): ?>
            <a href="<?= h($buildQuery($page - 1)) ?>"
                class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-gray-200 text-sm font-medium rounded-lg transition">
                Previous
            </a>
            <?php else: ?>
            <span class="px-3 py-2 bg-gray-800 text-gray-600 text-sm font-medium rounded-lg border border-gray-700 cursor-not-allowed">
                Previous
            </span>
            <?php endif; ?>

            <span class="px-3 py-2 text-sm text-gray-400">
                Page <span class="text-gray-200 font-medium"><?= number_format($page) ?></span> of <?= number_format($totalPages) ?>
            </span>

            <?php if ($page < $totalPages): ?>
            <a href="<?= h($buildQuery($page + 1)) ?>"
                class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-gray-200 text-sm font-medium rounded-lg transition">
                Next
            </a>
            <?php else: ?>
            <span class="px-3 py-2 bg-gray-800 text-gray-600 text-sm font-medium rounded-lg border border-gray-700 cursor-not-allowed">
                Next
            </span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/superadmin/layouts/layout.php';
?>
