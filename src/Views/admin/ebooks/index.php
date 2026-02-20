<?php
$pageTitle = 'Ebooks';
$currentPage = 'ebooks';
$tenant = currentTenant();
ob_start();
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-white">Ebooks</h2>
        <p class="text-sm text-gray-400 mt-1">Manage your digital ebook products.</p>
    </div>
    <a href="/admin/eboger/opret" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Create New
    </a>
</div>

<div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
    <?php if (empty($ebooks)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <p class="text-gray-400 mb-4">No ebooks yet. Publish your first ebook to start selling.</p>
            <a href="/admin/eboger/opret" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                Create Ebook
            </a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Views</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Downloads</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($ebooks as $ebook): ?>
                    <tr class="hover:bg-gray-750" x-data="{ confirmDelete: false }">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <?php if (!empty($ebook['cover_image'])): ?>
                                    <img src="<?= h($ebook['cover_image']) ?>" alt="" class="w-10 h-14 object-cover rounded mr-3 border border-gray-600">
                                <?php endif; ?>
                                <div>
                                    <div class="text-sm font-medium text-white"><?= h($ebook['title']) ?></div>
                                    <div class="text-xs text-gray-400 mt-0.5">/ebog/<?= h($ebook['slug']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($ebook['status'] === 'published'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-300">Published</span>
                            <?php elseif ($ebook['status'] === 'draft'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-900 text-yellow-300">Draft</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-300">Archived</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-300">
                            <?= $ebook['price'] > 0 ? formatMoney($ebook['price']) : '<span class="text-green-400">Free</span>' ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-300"><?= number_format($ebook['views'] ?? 0) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-300"><?= number_format($ebook['downloads'] ?? 0) ?></td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="/admin/eboger/rediger?id=<?= $ebook['id'] ?>" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                                    Edit
                                </a>
                                <template x-if="!confirmDelete">
                                    <button @click="confirmDelete = true" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-400 bg-gray-700 hover:bg-red-900/50 rounded-lg transition">
                                        Delete
                                    </button>
                                </template>
                                <template x-if="confirmDelete">
                                    <form method="POST" action="/admin/eboger/slet" class="inline-flex items-center space-x-1">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $ebook['id'] ?>">
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
