<?php
$pageTitle = 'Community Channels';
$currentPage = 'community';
$tenant = currentTenant();
ob_start();
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Community Channels</h2>
        <p class="text-sm text-gray-500 mt-1">Create and manage discussion channels for your community.</p>
    </div>
    <a href="/admin/community" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Community
    </a>
</div>

<!-- Add New Channel Form -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Add New Channel</h3>
    <form method="POST" action="/admin/community/channels">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="create">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-600">*</span></label>
                <input type="text" name="name" required
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm"
                       placeholder="e.g. General Discussion">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Read Tier Level</label>
                <select name="read_tier_level" class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="0">Free (0)</option>
                    <option value="1">Pro (1)</option>
                    <option value="2">Premium (2)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Post Tier Level</label>
                <select name="post_tier_level" class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="0">Free (0)</option>
                    <option value="1">Pro (1)</option>
                    <option value="2">Premium (2)</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <input type="text" name="description"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm"
                       placeholder="Brief description of this channel">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Sort Order</label>
                <input type="number" name="sort_order" value="0" min="0"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div class="flex items-end">
                <div class="flex items-center">
                    <input type="checkbox" id="is_locked_new" name="is_locked" value="1"
                           class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500">
                    <label for="is_locked_new" class="ml-2 text-sm font-medium text-gray-700">Locked (admin-only posting)</label>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                Create Channel
            </button>
        </div>
    </form>
</div>

<!-- Existing Channels -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Existing Channels</h3>
    </div>
    <?php if (empty($channels)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <p class="text-gray-500">No channels yet. Create your first channel above.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Read Tier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Post Tier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posts</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($channels as $channel): ?>
                    <tr class="hover:bg-gray-50 transition-colors" x-data="{ editing: false }">
                        <!-- View mode -->
                        <template x-if="!editing">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?= h($channel['name']) ?></div>
                                <?php if (!empty($channel['description'])): ?>
                                    <div class="text-xs text-gray-500 mt-0.5"><?= h($channel['description']) ?></div>
                                <?php endif; ?>
                            </td>
                        </template>
                        <template x-if="!editing">
                            <td class="px-6 py-4 text-sm text-gray-600 font-mono"><?= h($channel['slug']) ?></td>
                        </template>
                        <template x-if="!editing">
                            <td class="px-6 py-4">
                                <?php $rtl = (int)($channel['read_tier_level'] ?? 0); ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $rtl === 0 ? 'bg-gray-100 text-gray-700' : ($rtl === 1 ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700') ?>">
                                    <?= $rtl === 0 ? 'Free' : ($rtl === 1 ? 'Pro' : 'Premium') ?>
                                </span>
                            </td>
                        </template>
                        <template x-if="!editing">
                            <td class="px-6 py-4">
                                <?php $ptl = (int)($channel['post_tier_level'] ?? 0); ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $ptl === 0 ? 'bg-gray-100 text-gray-700' : ($ptl === 1 ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700') ?>">
                                    <?= $ptl === 0 ? 'Free' : ($ptl === 1 ? 'Pro' : 'Premium') ?>
                                </span>
                            </td>
                        </template>
                        <template x-if="!editing">
                            <td class="px-6 py-4 text-sm text-gray-600"><?= (int)($channel['post_count'] ?? 0) ?></td>
                        </template>
                        <template x-if="!editing">
                            <td class="px-6 py-4">
                                <?php if (!empty($channel['is_locked'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Locked</span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Open</span>
                                <?php endif; ?>
                            </td>
                        </template>
                        <template x-if="!editing">
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <button @click="editing = true" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                        Edit
                                    </button>
                                    <form method="POST" action="/admin/community/channels" onsubmit="return confirm('Delete this channel? All posts in this channel will also be deleted.')" class="inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $channel['id'] ?>">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-gray-100 hover:bg-red-50 rounded-lg transition">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </template>

                        <!-- Edit mode -->
                        <template x-if="editing">
                            <td colspan="7" class="px-6 py-4">
                                <form method="POST" action="/admin/community/channels">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="id" value="<?= $channel['id'] ?>">
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-600">*</span></label>
                                            <input type="text" name="name" required value="<?= h($channel['name']) ?>"
                                                   class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Read Tier Level</label>
                                            <select name="read_tier_level" class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                                                <option value="0" <?= (int)($channel['read_tier_level'] ?? 0) === 0 ? 'selected' : '' ?>>Free (0)</option>
                                                <option value="1" <?= (int)($channel['read_tier_level'] ?? 0) === 1 ? 'selected' : '' ?>>Pro (1)</option>
                                                <option value="2" <?= (int)($channel['read_tier_level'] ?? 0) === 2 ? 'selected' : '' ?>>Premium (2)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Post Tier Level</label>
                                            <select name="post_tier_level" class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                                                <option value="0" <?= (int)($channel['post_tier_level'] ?? 0) === 0 ? 'selected' : '' ?>>Free (0)</option>
                                                <option value="1" <?= (int)($channel['post_tier_level'] ?? 0) === 1 ? 'selected' : '' ?>>Pro (1)</option>
                                                <option value="2" <?= (int)($channel['post_tier_level'] ?? 0) === 2 ? 'selected' : '' ?>>Premium (2)</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                            <input type="text" name="description" value="<?= h($channel['description'] ?? '') ?>"
                                                   class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                                            <input type="number" name="sort_order" value="<?= (int)($channel['sort_order'] ?? 0) ?>" min="0"
                                                   class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                                        </div>
                                        <div class="flex items-end">
                                            <div class="flex items-center">
                                                <input type="checkbox" name="is_locked" value="1"
                                                       class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500"
                                                       <?= !empty($channel['is_locked']) ? 'checked' : '' ?>>
                                                <label class="ml-2 text-sm font-medium text-gray-700">Locked</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-3 mt-4">
                                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                                            Save Changes
                                        </button>
                                        <button type="button" @click="editing = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </template>
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
