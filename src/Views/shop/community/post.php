<?php
$pageTitle = h($post['title'] ?? 'Post') . ' — Community';
$metaDescription = truncate($post['body'] ?? '', 160);
$tenant = $tenant ?? currentTenant();
$post = $post ?? [];
$comments = $comments ?? [];
$channel = $channel ?? [];
$canPost = $canPost ?? false;
$likedPostIds = $likedPostIds ?? [];
$likedCommentIds = $likedCommentIds ?? [];
$isPostLiked = in_array((int)$post['id'], $likedPostIds);
$postLikeCount = (int)($post['like_count'] ?? 0);
$commentCount = (int)($post['comment_count'] ?? 0);
ob_start();

// Avatar color helper
$avatarColors = ['bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-pink-500', 'bg-amber-500', 'bg-teal-500', 'bg-red-500', 'bg-indigo-500'];
function communityAvatarColor($name, $colors) {
    $initial = mb_strtoupper(mb_substr($name ?: '?', 0, 1));
    return $colors[ord($initial) % count($colors)];
}
?>

<section class="py-12 lg:py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Back Link -->
        <a href="/community/<?= h($post['channel_slug'] ?? $channel['slug'] ?? '') ?>" class="inline-flex items-center text-sm text-gray-400 hover:text-gray-600 transition mb-6">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to <?= h($post['channel_name'] ?? $channel['name'] ?? 'Channel') ?>
        </a>

        <!-- Post Card -->
        <?php
            $postInitial = mb_strtoupper(mb_substr($post['author_name'] ?? '?', 0, 1));
            $postAvatarColor = communityAvatarColor($post['author_name'] ?? '', $avatarColors);
        ?>
        <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8 mb-8">
            <!-- Author + Timestamp -->
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-full <?= $postAvatarColor ?> flex items-center justify-center flex-shrink-0">
                    <span class="text-white text-sm font-bold"><?= h($postInitial) ?></span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900"><?= h($post['author_name'] ?? 'Anonymous') ?></p>
                    <p class="text-xs text-gray-400"><?= formatDate($post['created_at'] ?? '', 'd M Y, H:i') ?></p>
                </div>
                <?php if (!empty($post['is_pinned'])): ?>
                    <span class="ml-auto inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand/10 text-brand">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2H5V5zm0 4h10v6a2 2 0 01-2 2H7a2 2 0 01-2-2V9z"/></svg>
                        Pinned
                    </span>
                <?php endif; ?>
            </div>

            <!-- Post Title + Body -->
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4"><?= h($post['title']) ?></h1>
            <div class="text-gray-700 leading-relaxed whitespace-pre-line text-sm sm:text-base"><?= h($post['body'] ?? '') ?></div>

            <!-- Post Actions -->
            <div class="flex items-center gap-5 mt-6 pt-4 border-t border-gray-100"
                 x-data="{ liked: <?= $isPostLiked ? 'true' : 'false' ?>, count: <?= $postLikeCount ?> }">
                <!-- Like Button -->
                <button @click="
                    fetch('/api/community/like', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ post_id: <?= (int)$post['id'] ?>, '<?= CSRF_TOKEN_NAME ?>': '<?= generateCsrfToken() ?>' })
                    }).then(r => r.json()).then(data => {
                        if (data.success) { liked = data.liked; count = data.count; }
                    })
                " class="flex items-center gap-1.5 text-sm transition"
                   :class="liked ? 'text-red-500' : 'text-gray-400 hover:text-red-500'">
                    <svg class="w-5 h-5" :fill="liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    <span x-text="count" class="font-medium"></span>
                </button>

                <!-- Comment Count -->
                <span class="flex items-center gap-1.5 text-sm text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span class="font-medium"><?= $commentCount ?> comment<?= $commentCount !== 1 ? 's' : '' ?></span>
                </span>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="mb-8">
            <h2 class="text-lg font-bold text-gray-900 mb-6">Comments</h2>

            <?php if (empty($comments)): ?>
                <div class="text-center py-8">
                    <p class="text-gray-400 text-sm">No comments yet. Be the first to share your thoughts.</p>
                </div>
            <?php else: ?>
                <div class="space-y-1">
                    <?php foreach ($comments as $comment): ?>
                        <?php
                            $isReply = !empty($comment['parent_id']);
                            $commentInitial = mb_strtoupper(mb_substr($comment['author_name'] ?? '?', 0, 1));
                            $commentAvatarColor = communityAvatarColor($comment['author_name'] ?? '', $avatarColors);
                            $isCommentLiked = in_array((int)$comment['id'], $likedCommentIds);
                            $commentLikeCount = (int)($comment['like_count'] ?? 0);
                        ?>
                        <div class="<?= $isReply ? 'ml-8 sm:ml-12' : '' ?>"
                             x-data="{ showReply: false, cLiked: <?= $isCommentLiked ? 'true' : 'false' ?>, cCount: <?= $commentLikeCount ?> }">
                            <div class="bg-white rounded-xl border border-gray-200 p-5 mb-3">
                                <!-- Comment Author -->
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-8 h-8 rounded-full <?= $commentAvatarColor ?> flex items-center justify-center flex-shrink-0">
                                        <span class="text-white text-xs font-bold"><?= h($commentInitial) ?></span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900"><?= h($comment['author_name'] ?? 'Anonymous') ?></p>
                                        <p class="text-xs text-gray-400"><?= formatDate($comment['created_at'] ?? '', 'd M Y, H:i') ?></p>
                                    </div>
                                </div>

                                <!-- Comment Body -->
                                <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-line"><?= h($comment['body'] ?? '') ?></div>

                                <!-- Comment Actions -->
                                <div class="flex items-center gap-4 mt-3 pt-2 border-t border-gray-50">
                                    <!-- Like -->
                                    <button @click="
                                        fetch('/api/community/like', {
                                            method: 'POST',
                                            headers: { 'Content-Type': 'application/json' },
                                            body: JSON.stringify({ comment_id: <?= (int)$comment['id'] ?>, '<?= CSRF_TOKEN_NAME ?>': '<?= generateCsrfToken() ?>' })
                                        }).then(r => r.json()).then(data => {
                                            if (data.success) { cLiked = data.liked; cCount = data.count; }
                                        })
                                    " class="flex items-center gap-1 text-xs transition"
                                       :class="cLiked ? 'text-red-500' : 'text-gray-400 hover:text-red-500'">
                                        <svg class="w-3.5 h-3.5" :fill="cLiked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                        <span x-text="cCount" class="font-medium"></span>
                                    </button>

                                    <!-- Reply Button -->
                                    <?php if ($canPost && empty($post['is_locked'])): ?>
                                        <button @click="showReply = !showReply" class="flex items-center gap-1 text-xs text-gray-400 hover:text-gray-600 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                            Reply
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <!-- Reply Form (inline) -->
                                <?php if ($canPost && empty($post['is_locked'])): ?>
                                    <div x-show="showReply" x-cloak x-transition class="mt-3 pt-3 border-t border-gray-100">
                                        <form action="/api/community/comment" method="POST" class="space-y-3">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                                            <input type="hidden" name="parent_id" value="<?= (int)$comment['id'] ?>">
                                            <textarea name="body" rows="2" required
                                                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand focus:ring-1 focus:ring-brand outline-none transition resize-y"
                                                      placeholder="Write a reply..."></textarea>
                                            <div class="flex items-center gap-2 justify-end">
                                                <button type="button" @click="showReply = false" class="px-3 py-1.5 text-xs text-gray-500 hover:text-gray-700 transition">Cancel</button>
                                                <button type="submit" class="btn-brand px-4 py-1.5 text-white text-xs font-semibold rounded-lg transition">Reply</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add Comment Form -->
        <?php if (!empty($post['is_locked'])): ?>
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <p class="text-sm text-gray-500">Comments are locked on this post.</p>
            </div>
        <?php elseif ($canPost): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Add a Comment</h3>
                <form action="/api/community/comment" method="POST" class="space-y-4">
                    <?= csrfField() ?>
                    <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                    <div>
                        <textarea name="body" rows="3" required
                                  class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand outline-none transition resize-y"
                                  placeholder="Share your thoughts..."></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn-brand px-6 py-2.5 text-white text-sm font-semibold rounded-lg transition">
                            Post Comment
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
