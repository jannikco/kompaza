<?php
$pageTitle = 'A/B Tests';
$currentPage = 'ab-tests';
ob_start();

$statusColors = ['draft' => 'bg-gray-100 text-gray-700', 'running' => 'bg-green-100 text-green-700', 'paused' => 'bg-yellow-100 text-yellow-700', 'completed' => 'bg-blue-100 text-blue-700'];
?>

<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">Split test your pages to optimize conversion rates.</p>
    <a href="/admin/ab-tests/create" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New A/B Test
    </a>
</div>

<?php if (empty($tests)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
    <h3 class="text-lg font-medium text-gray-900 mb-2">No A/B tests yet</h3>
    <p class="text-gray-500 mb-4">Create split tests to compare different versions of your pages and find what converts best.</p>
    <a href="/admin/ab-tests/create" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">Create A/B Test</a>
</div>
<?php else: ?>
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Test Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Variants</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Started</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php foreach ($tests as $t): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= h($t['name']) ?></td>
                <td class="px-6 py-4 text-sm text-gray-500"><?= ucfirst(str_replace('_', ' ', $t['test_type'])) ?></td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium <?= $statusColors[$t['status']] ?? 'bg-gray-100 text-gray-700' ?>">
                        <?= ucfirst($t['status']) ?>
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900"><?= (int)$t['variant_count'] ?></td>
                <td class="px-6 py-4 text-sm text-gray-500"><?= $t['started_at'] ? formatDate($t['started_at']) : '-' ?></td>
                <td class="px-6 py-4 text-right">
                    <a href="/admin/ab-tests/edit?id=<?= $t['id'] ?>" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
