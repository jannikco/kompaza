<?php
$pageTitle = 'Email Signups';
$currentPage = 'signups';
$tenant = currentTenant();
ob_start();
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Email Signups</h2>
        <p class="text-sm text-gray-500 mt-1">View and manage all email signups from your lead magnets and forms.</p>
    </div>
    <a href="/admin/tilmeldinger/eksport" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition border border-gray-300">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Export CSV
    </a>
</div>

<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <?php if (empty($signups)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <p class="text-gray-500">No signups yet. Once visitors sign up through your lead magnets or forms, they will appear here.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($signups as $signup): ?>
                    <tr class="hover:bg-gray-50" x-data="{ confirmDelete: false }">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900"><?= h($signup['email']) ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= h($signup['name'] ?? '-') ?></td>
                        <td class="px-6 py-4">
                            <?php
                            $sourceType = $signup['source_type'] ?? 'unknown';
                            $badgeClass = match($sourceType) {
                                'lead_magnet' => 'bg-purple-100 text-purple-700',
                                'newsletter' => 'bg-blue-100 text-blue-700',
                                'ebook' => 'bg-orange-100 text-orange-700',
                                'form' => 'bg-green-100 text-green-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badgeClass ?>">
                                <?= h(ucfirst(str_replace('_', ' ', $sourceType))) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= h($signup['source_name'] ?? $signup['source'] ?? '-') ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= formatDate($signup['created_at'] ?? '', 'd M Y H:i') ?></td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <template x-if="!confirmDelete">
                                    <button @click="confirmDelete = true" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-gray-100 hover:bg-red-50 rounded-lg transition">
                                        Delete
                                    </button>
                                </template>
                                <template x-if="confirmDelete">
                                    <form method="POST" action="/admin/tilmeldinger/slet" class="inline-flex items-center space-x-1">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $signup['id'] ?>">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                                            Confirm
                                        </button>
                                        <button type="button" @click="confirmDelete = false" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                            Cancel
                                        </button>
                                    </form>
                                </template>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (isset($totalPages) && $totalPages > 1): ?>
        <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Showing <?= (($currentPageNum - 1) * $perPage) + 1 ?> to <?= min($currentPageNum * $perPage, $totalSignups) ?> of <?= number_format($totalSignups) ?> signups
            </div>
            <div class="flex items-center space-x-1">
                <?php if ($currentPageNum > 1): ?>
                    <a href="/admin/tilmeldinger?page=<?= $currentPageNum - 1 ?>" class="px-3 py-1.5 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                        Previous
                    </a>
                <?php endif; ?>

                <?php
                $startPage = max(1, $currentPageNum - 2);
                $endPage = min($totalPages, $currentPageNum + 2);
                ?>

                <?php if ($startPage > 1): ?>
                    <a href="/admin/tilmeldinger?page=1" class="px-3 py-1.5 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">1</a>
                    <?php if ($startPage > 2): ?>
                        <span class="px-2 text-gray-500">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="/admin/tilmeldinger?page=<?= $i ?>"
                        class="px-3 py-1.5 text-sm rounded-lg transition <?= $i === $currentPageNum ? 'bg-indigo-600 text-white' : 'text-gray-700 bg-gray-100 hover:bg-gray-200' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span class="px-2 text-gray-500">...</span>
                    <?php endif; ?>
                    <a href="/admin/tilmeldinger?page=<?= $totalPages ?>" class="px-3 py-1.5 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition"><?= $totalPages ?></a>
                <?php endif; ?>

                <?php if ($currentPageNum < $totalPages): ?>
                    <a href="/admin/tilmeldinger?page=<?= $currentPageNum + 1 ?>" class="px-3 py-1.5 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                        Next
                    </a>
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
