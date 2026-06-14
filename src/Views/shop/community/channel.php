<?php
$pageTitle = h($channel['name'] ?? 'Channel') . ' — Community';
$metaDescription = $channel['description'] ?? 'Community channel posts and discussions.';
$tenant = $tenant ?? currentTenant();
$channel = $channel ?? [];
$posts = $posts ?? [];
$canPost = $canPost ?? false;
$page = $page ?? 1;
$totalPosts = $totalPosts ?? 0;
$userTierLevel = $userTierLevel ?? 0;
$likedPostIds = $likedPostIds ?? [];
$perPage = 20;
$totalPages = max(1, ceil($totalPosts / $perPage));
ob_start();
?>

<section class="py-12 lg:py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Back Link + Header -->
        <div class="mb-8">
            <a href="/community" class="inline-flex items-center text-sm text-gray-400 hover:text-gray-600 transition mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Community
            </a>
            <div class="flex items-start gap-4">
                <?php if (!empty($channel['icon'])): ?>
                    <div class="w-12 h-12 rounded-xl bg-brand/10 flex items-center justify-center flex-shrink-0">
                        <span class="text-2xl"><?= h($channel['icon']) ?></span>
                    </div>
                <?php endif; ?>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= h($channel['name']) ?></h1>
                    <?php if (!empty($channel['description'])): ?>
                        <p class="mt-1 text-gray-500"><?= h($channel['description']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- New Post Form -->
        <?php if (!empty($channel['is_locked']) && !$canPost): ?>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-8 flex items-center gap-3">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <p class="text-sm text-amber-700">This channel is read-only. You can browse posts but cannot create new ones.</p>
            </div>
        <?php elseif ($canPost): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-8" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full text-left">
                    <span class="text-sm font-semibold text-gray-900">New Post</span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <form action="/api/community/post" method="POST" x-show="open" x-cloak x-transition class="mt-4 space-y-4">
                    <?= csrfField() ?>
                    <input type="hidden" name="channel_id" value="<?= (int)$channel['id'] ?>">
                    <div>
                        <label for="post-title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" id="post-title" name="title" required
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand outline-none transition"
                               placeholder="What's on your mind?">
                    </div>
                    <div>
                        <label for="post-body" class="block text-sm font-medium text-gray-700 mb-1">Body</label>
                        <textarea id="post-body" name="body" rows="4" required
                                  class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand outline-none transition resize-y"
                                  placeholder="Share your thoughts, questions, or ideas..."></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn-brand px-6 py-2.5 text-white text-sm font-semibold rounded-lg transition">
                            Publish Post
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Posts List -->
        <?php if (empty($posts)): ?>
            <div class="text-center py-16">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
                <p class="text-gray-500 text-lg">No posts yet.</p>
                <p class="text-gray-400 text-sm mt-1">Be the first to start a conversation.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($posts as $post): ?>
                    <?php
                        $isLiked = in_array((int)$post['id'], $likedPostIds);
                        $likeCount = (int)($post['like_count'] ?? 0);
                        $commentCount = (int)($post['comment_count'] ?? 0);
                        $initial = mb_strtoupper(mb_substr($post['author_name'] ?? '?', 0, 1));
                        $colors = ['bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-pink-500', 'bg-amber-500', 'bg-teal-500', 'bg-red-500', 'bg-indigo-500'];
                        $colorIndex = ord($initial) % count($colors);
                        $avatarColor = $colors[$colorIndex];
                    ?>
                    <div class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-sm transition">
                        <!-- Author + Timestamp -->
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-full <?= $avatarColor ?> flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-sm font-bold"><?= h($initial) ?></span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate"><?= h($post['author_name'] ?? 'Anonymous') ?></p>
                                <p class="text-xs text-gray-400"><?= formatDate($post['created_at'] ?? '', 'd M Y, H:i') ?></p>
                            </div>
                            <?php if (!empty($post['is_pinned'])): ?>
                                <span class="ml-auto inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand/10 text-brand flex-shrink-0">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2H5V5zm0 4h10v6a2 2 0 01-2 2H7a2 2 0 01-2-2V9z"/></svg>
                                    Pinned
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Post Content -->
                        <a href="/community/<?= h($channel['slug']) ?>/<?= (int)$post['id'] ?>" class="block group">
                            <h2 class="text-lg font-semibold text-gray-900 group-hover:text-brand transition mb-2"><?= h($post['title']) ?></h2>
                            <p class="text-sm text-gray-600 leading-relaxed"><?= h(truncate($post['body'] ?? '', 200)) ?></p>
                        </a>

                        <!-- Actions -->
                        <div class="flex items-center gap-5 mt-4 pt-3 border-t border-gray-100"
                             x-data="{ liked: <?= $isLiked ? 'true' : 'false' ?>, count: <?= $likeCount ?> }">
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
                                <svg class="w-4.5 h-4.5" :fill="liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                <span x-text="count" class="font-medium"></span>
                            </button>

                            <!-- Comment Count -->
                            <a href="/community/<?= h($channel['slug']) ?>/<?= (int)$post['id'] ?>" class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-600 transition">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <span class="font-medium"><?= $commentCount ?></span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                    <?php if ($page > 1): ?>
                        <a href="/community/<?= h($channel['slug']) ?>?page=<?= $page - 1 ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Previous
                        </a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>

                    <span class="text-sm text-gray-500">Page <?= $page ?> of <?= $totalPages ?></span>

                    <?php if ($page < $totalPages): ?>
                        <a href="/community/<?= h($channel['slug']) ?>?page=<?= $page + 1 ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Next
                            <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
