<?php
$pageTitle = 'Articles';
$currentPage = 'articles';
$tenant = currentTenant();
ob_start();
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-white">Articles</h2>
        <p class="text-sm text-gray-400 mt-1">Manage your blog posts and articles.</p>
    </div>
    <a href="/admin/artikler/opret" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Create New
    </a>
</div>

<div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
    <?php if (empty($articles)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            <p class="text-gray-400 mb-4">No articles yet. Write your first article to get started.</p>
            <a href="/admin/artikler/opret" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                Create Article
            </a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Views</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Published</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($articles as $article): ?>
                    <tr class="hover:bg-gray-750" x-data="{ confirmDelete: false }">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-white"><?= h($article['title']) ?></div>
                            <div class="text-xs text-gray-400 mt-0.5">/blog/<?= h($article['slug']) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($article['status'] === 'published'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-300">Published</span>
                            <?php elseif ($article['status'] === 'draft'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-900 text-yellow-300">Draft</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-300">Archived</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-300"><?= h($article['category'] ?? '-') ?></td>
                        <td class="px-6 py-4 text-sm text-gray-300"><?= number_format($article['views'] ?? 0) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-300"><?= $article['published_at'] ? formatDate($article['published_at']) : '-' ?></td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="/admin/artikler/rediger?id=<?= $article['id'] ?>" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                                    Edit
                                </a>
                                <template x-if="!confirmDelete">
                                    <button @click="confirmDelete = true" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-400 bg-gray-700 hover:bg-red-900/50 rounded-lg transition">
                                        Delete
                                    </button>
                                </template>
                                <template x-if="confirmDelete">
                                    <form method="POST" action="/admin/artikler/slet" class="inline-flex items-center space-x-1">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $article['id'] ?>">
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
