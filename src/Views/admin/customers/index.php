<?php
$pageTitle = 'Customers';
$currentPage = 'customers';
$tenant = currentTenant();
ob_start();
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-white">Customers</h2>
        <p class="text-sm text-gray-400 mt-1">Manage your customer accounts and information.</p>
    </div>
    <div class="flex items-center space-x-3">
        <a href="/admin/kunder/eksport" class="inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-200 text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export CSV
        </a>
        <a href="/admin/kunder/opret" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Customer
        </a>
    </div>
</div>

<!-- Search Bar -->
<div class="mb-6">
    <form method="GET" action="/admin/kunder" class="flex items-center gap-3">
        <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text" name="search" value="<?= h($search ?? '') ?>" placeholder="Search by name, email, or company..." class="w-full pl-10 pr-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
        </div>
        <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition">
            Search
        </button>
        <?php if (!empty($search)): ?>
            <a href="/admin/kunder" class="inline-flex items-center px-4 py-2.5 text-sm text-gray-400 hover:text-white transition">
                Clear
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
    <?php if (empty($customers)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <p class="text-gray-400 mb-4">No customers found. Add your first customer to get started.</p>
            <a href="/admin/kunder/opret" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                Add Customer
            </a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700 bg-gray-800/50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Company</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($customers as $customer): ?>
                    <tr class="hover:bg-gray-750 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-white"><?= h(mb_strtoupper(mb_substr($customer['name'] ?? '?', 0, 1))) ?></span>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-white"><?= h($customer['name']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-300"><?= h($customer['email']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-300"><?= h($customer['company'] ?? '-') ?></td>
                        <td class="px-6 py-4 text-sm text-gray-300"><?= h($customer['phone'] ?? '-') ?></td>
                        <td class="px-6 py-4 text-sm text-gray-300"><?= (int)($customer['order_count'] ?? 0) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-400"><?= formatDate($customer['created_at']) ?></td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="/admin/kunder/<?= $customer['id'] ?>" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                                    View
                                </a>
                                <a href="/admin/kunder/rediger?id=<?= $customer['id'] ?>" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($totalPages) && $totalPages > 1): ?>
        <div class="px-6 py-4 border-t border-gray-700 flex items-center justify-between">
            <div class="text-sm text-gray-400">
                Showing <?= (($currentPageNum - 1) * $perPage) + 1 ?> to <?= min($currentPageNum * $perPage, $totalCustomers) ?> of <?= $totalCustomers ?> customers
            </div>
            <div class="flex items-center space-x-1">
                <?php if ($currentPageNum > 1): ?>
                    <a href="/admin/kunder?page=<?= $currentPageNum - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-1.5 text-sm text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">Previous</a>
                <?php endif; ?>

                <?php for ($i = max(1, $currentPageNum - 2); $i <= min($totalPages, $currentPageNum + 2); $i++): ?>
                    <a href="/admin/kunder?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                       class="px-3 py-1.5 text-sm rounded-lg transition <?= $i === $currentPageNum ? 'bg-indigo-600 text-white' : 'text-gray-300 bg-gray-700 hover:bg-gray-600' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($currentPageNum < $totalPages): ?>
                    <a href="/admin/kunder?page=<?= $currentPageNum + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="px-3 py-1.5 text-sm text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">Next</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
