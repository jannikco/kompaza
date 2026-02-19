<?php
$pageTitle = 'Lead Magnets';
$currentPage = 'lead-magnets';
$tenant = currentTenant();
ob_start();
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-white">Lead Magnets</h2>
        <p class="text-sm text-gray-400 mt-1">Manage your lead magnet landing pages and downloadable content.</p>
    </div>
    <a href="/admin/lead-magnets/opret" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Create New
    </a>
</div>

<div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
    <?php if (empty($leadMagnets)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-gray-400 mb-4">No lead magnets yet. Create your first one to start capturing leads.</p>
            <a href="/admin/lead-magnets/opret" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                Create Lead Magnet
            </a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Signups</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Views</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($leadMagnets as $lm): ?>
                    <tr class="hover:bg-gray-750" x-data="{ confirmDelete: false }">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-white"><?= h($lm['title']) ?></div>
                            <div class="text-xs text-gray-400 mt-0.5">/lp/<?= h($lm['slug']) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($lm['status'] === 'published'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-300">Published</span>
                            <?php elseif ($lm['status'] === 'draft'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-900 text-yellow-300">Draft</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-300">Archived</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-300"><?= number_format($lm['signup_count'] ?? 0) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-300"><?= number_format($lm['views'] ?? 0) ?></td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="/admin/lead-magnets/rediger/<?= $lm['id'] ?>" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                                    Edit
                                </a>
                                <template x-if="!confirmDelete">
                                    <button @click="confirmDelete = true" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-400 bg-gray-700 hover:bg-red-900/50 rounded-lg transition">
                                        Delete
                                    </button>
                                </template>
                                <template x-if="confirmDelete">
                                    <form method="POST" action="/admin/lead-magnets/slet/<?= $lm['id'] ?>" class="inline-flex items-center space-x-1">
                                        <?= csrfField() ?>
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                                            Confirm
                                        </button>
                                        <button type="button" @click="confirmDelete = false" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
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
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
