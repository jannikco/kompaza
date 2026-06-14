<?php
$pageTitle = 'Community';
$currentPage = 'community';
$tenant = currentTenant();
ob_start();
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Community</h2>
        <p class="text-sm text-gray-500 mt-1">Moderate posts and manage your community.</p>
    </div>
    <a href="/admin/community/channels" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        Manage Channels
    </a>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Channels</p>
        <p class="text-xl font-bold text-gray-900 mt-1"><?= (int)($totalChannels ?? 0) ?></p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Recent Posts (7 days)</p>
        <p class="text-xl font-bold text-gray-900 mt-1"><?= (int)($recentPostsCount ?? 0) ?></p>
    </div>
</div>

<!-- Hidden/Flagged Posts -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Hidden / Flagged Posts</h3>
        <p class="text-sm text-gray-500 mt-0.5">Posts that have been hidden or flagged for review.</p>
    </div>
    <?php if (empty($hiddenPosts)): ?>
        <div class="p-8 text-center">
            <svg class="w-10 h-10 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-gray-500">No hidden or flagged posts. All clear!</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Channel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($hiddenPosts as $post): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900"><?= h($post['title'] ?? 'Untitled') ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= h($post['author_name'] ?? '-') ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= h($post['channel_name'] ?? '-') ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= formatDate($post['created_at']) ?></td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <form method="POST" action="/admin/community/moderate" class="inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <input type="hidden" name="action" value="show">
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                        Show
                                    </button>
                                </form>
                                <form method="POST" action="/admin/community/moderate" class="inline" onsubmit="return confirm('Permanently delete this post?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-gray-100 hover:bg-red-50 rounded-lg transition">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Recent Posts -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Recent Posts</h3>
        <p class="text-sm text-gray-500 mt-0.5">Latest community activity across all channels.</p>
    </div>
    <?php if (empty($recentPosts)): ?>
        <div class="p-8 text-center">
            <svg class="w-10 h-10 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <p class="text-gray-500">No posts yet. Members will start posting once channels are set up.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Channel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Likes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comments</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($recentPosts as $post): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900"><?= h($post['title'] ?? 'Untitled') ?></div>
                            <?php if (!empty($post['is_pinned'])): ?>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 mt-1">Pinned</span>
                            <?php endif; ?>
                            <?php if (!empty($post['is_locked'])): ?>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 mt-1">Locked</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= h($post['author_name'] ?? '-') ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= h($post['channel_name'] ?? '-') ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= (int)($post['like_count'] ?? 0) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= (int)($post['comment_count'] ?? 0) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= formatDate($post['created_at']) ?></td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <form method="POST" action="/admin/community/moderate" class="inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <input type="hidden" name="action" value="hide">
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                        Hide
                                    </button>
                                </form>
                                <form method="POST" action="/admin/community/moderate" class="inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <input type="hidden" name="action" value="<?= !empty($post['is_pinned']) ? 'unpin' : 'pin' ?>">
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                        <?= !empty($post['is_pinned']) ? 'Unpin' : 'Pin' ?>
                                    </button>
                                </form>
                                <form method="POST" action="/admin/community/moderate" class="inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <input type="hidden" name="action" value="<?= !empty($post['is_locked']) ? 'unlock' : 'lock' ?>">
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                        <?= !empty($post['is_locked']) ? 'Unlock' : 'Lock' ?>
                                    </button>
                                </form>
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
