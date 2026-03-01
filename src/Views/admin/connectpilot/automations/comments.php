<?php $pageTitle = 'Comments — ' . h($automation['name']); $currentPage = 'connectpilot-automations'; $tenant = currentTenant(); ob_start(); ?>

<div class="mb-6">
    <a href="/admin/connectpilot/automations" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-900 transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Post Automations
    </a>
</div>

<!-- Automation Summary -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900"><?= h($automation['name']) ?></h2>
            <p class="text-sm text-gray-500 mt-1">
                Keyword: <span class="font-medium text-indigo-600"><?= h($automation['trigger_keyword']) ?></span>
                &middot;
                <a href="<?= h($automation['post_url']) ?>" target="_blank" class="text-indigo-600 hover:text-indigo-500">View Post</a>
            </p>
        </div>
        <a href="/admin/connectpilot/automations/edit?id=<?= $automation['id'] ?>" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
            Edit Automation
        </a>
    </div>
    <div class="grid grid-cols-5 gap-4 mt-4">
        <div class="text-center">
            <p class="text-lg font-bold text-gray-900"><?= number_format($automation['comments_detected'] ?? 0) ?></p>
            <p class="text-xs text-gray-500">Comments</p>
        </div>
        <div class="text-center">
            <p class="text-lg font-bold text-indigo-600"><?= number_format($automation['keyword_matches'] ?? 0) ?></p>
            <p class="text-xs text-gray-500">Matches</p>
        </div>
        <div class="text-center">
            <p class="text-lg font-bold text-blue-600"><?= number_format($automation['replies_sent'] ?? 0) ?></p>
            <p class="text-xs text-gray-500">Replies</p>
        </div>
        <div class="text-center">
            <p class="text-lg font-bold text-purple-600"><?= number_format($automation['dms_sent'] ?? 0) ?></p>
            <p class="text-xs text-gray-500">DMs Sent</p>
        </div>
        <div class="text-center">
            <p class="text-lg font-bold text-green-600"><?= number_format($automation['leads_captured'] ?? 0) ?></p>
            <p class="text-xs text-gray-500">Leads</p>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="flex items-center space-x-2 mb-4">
    <a href="/admin/connectpilot/automations/comments?id=<?= $automation['id'] ?>"
       class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg transition <?= $filter === 'all' ? 'bg-indigo-600 text-white' : 'text-gray-700 bg-gray-100 hover:bg-gray-200' ?>">
        All
    </a>
    <a href="/admin/connectpilot/automations/comments?id=<?= $automation['id'] ?>&filter=matched"
       class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg transition <?= $filter === 'matched' ? 'bg-indigo-600 text-white' : 'text-gray-700 bg-gray-100 hover:bg-gray-200' ?>">
        Matched Only
    </a>
    <a href="/admin/connectpilot/automations/comments?id=<?= $automation['id'] ?>&filter=pending_dm"
       class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg transition <?= $filter === 'pending_dm' ? 'bg-indigo-600 text-white' : 'text-gray-700 bg-gray-100 hover:bg-gray-200' ?>">
        Pending DM
    </a>
</div>

<!-- Comments Table -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <?php if (empty($comments)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            <p class="text-gray-500">No comments detected yet. The cron job checks every 5 minutes.</p>
            <?php if ($automation['last_checked_at']): ?>
            <p class="text-xs text-gray-400 mt-2">Last checked: <?= formatDate($automation['last_checked_at'], 'd M Y H:i') ?></p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commenter</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Match</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Reply</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">DM</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Lead</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($comments as $comment): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900"><?= h($comment['commenter_name'] ?? 'Unknown') ?></div>
                            <?php if ($comment['commenter_headline']): ?>
                            <div class="text-xs text-gray-500 mt-0.5 truncate max-w-xs"><?= h(truncate($comment['commenter_headline'], 50)) ?></div>
                            <?php endif; ?>
                            <?php if ($comment['commenter_profile_url']): ?>
                            <a href="<?= h($comment['commenter_profile_url']) ?>" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-500">View Profile</a>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-700 max-w-xs truncate"><?= h($comment['comment_text'] ?? '') ?></div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if ($comment['keyword_matched']): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Yes</span>
                            <?php else: ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">No</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if ($comment['reply_sent']): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Sent</span>
                            <?php elseif ($comment['keyword_matched']): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Pending</span>
                            <?php else: ?>
                            <span class="text-xs text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if ($comment['dm_sent']): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">Sent</span>
                            <?php elseif ($comment['keyword_matched']): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Pending</span>
                            <?php else: ?>
                            <span class="text-xs text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if ($comment['lead_id']): ?>
                            <a href="/admin/connectpilot/leads?search=<?= urlencode($comment['commenter_name'] ?? '') ?>" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 hover:bg-green-200">
                                Created
                            </a>
                            <?php else: ?>
                            <span class="text-xs text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= formatDate($comment['created_at'], 'd M H:i') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                Page <?= $currentPage ?> of <?= $totalPages ?> (<?= number_format($totalComments) ?> comments)
            </p>
            <div class="flex space-x-2">
                <?php if ($currentPage > 1): ?>
                <a href="/admin/connectpilot/automations/comments?id=<?= $automation['id'] ?>&page=<?= $currentPage - 1 ?>&filter=<?= $filter ?>" class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Previous</a>
                <?php endif; ?>
                <?php if ($currentPage < $totalPages): ?>
                <a href="/admin/connectpilot/automations/comments?id=<?= $automation['id'] ?>&page=<?= $currentPage + 1 ?>&filter=<?= $filter ?>" class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Next</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
