<?php
$pageTitle = 'Community';
$metaDescription = 'Join the community — explore channels, share ideas, and connect with other members.';
$tenant = $tenant ?? currentTenant();
$channels = $channels ?? [];
$userTierLevel = $userTierLevel ?? 0;
ob_start();
?>

<section class="py-12 lg:py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="text-center mb-12">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Community</h1>
            <p class="mt-3 text-lg text-gray-500 max-w-2xl mx-auto">Connect, share, and learn together. Join the conversation in our community channels.</p>
        </div>

        <?php if (empty($channels)): ?>
            <div class="text-center py-16">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-gray-500 text-lg">No community channels available yet.</p>
                <p class="text-gray-400 text-sm mt-1">Check back soon — new channels are on the way.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($channels as $channel): ?>
                    <?php $hasAccess = ((int)($channel['read_tier_level'] ?? 0)) <= $userTierLevel; ?>

                    <?php if ($hasAccess): ?>
                        <a href="/community/<?= h($channel['slug']) ?>" class="group bg-white rounded-xl border border-gray-200 p-6 hover:shadow-lg hover:border-brand/30 transition-all duration-300">
                    <?php else: ?>
                        <div class="bg-white rounded-xl border border-gray-200 p-6 opacity-75">
                    <?php endif; ?>

                        <div class="flex items-start gap-4">
                            <!-- Channel Icon -->
                            <div class="w-12 h-12 rounded-xl bg-brand/10 flex items-center justify-center flex-shrink-0 <?= $hasAccess ? 'group-hover:scale-110 transition-transform' : '' ?>">
                                <?php if (!empty($channel['icon'])): ?>
                                    <span class="text-2xl"><?= h($channel['icon']) ?></span>
                                <?php else: ?>
                                    <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                    </svg>
                                <?php endif; ?>
                            </div>

                            <!-- Channel Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h2 class="text-lg font-semibold text-gray-900 truncate <?= $hasAccess ? 'group-hover:text-brand transition' : '' ?>"><?= h($channel['name']) ?></h2>
                                    <?php if (!empty($channel['is_locked'])): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Read Only</span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($channel['description'])): ?>
                                    <p class="text-sm text-gray-500 mb-3 line-clamp-2"><?= h($channel['description']) ?></p>
                                <?php endif; ?>

                                <div class="flex items-center gap-4 text-xs text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                        <?= (int)($channel['post_count'] ?? 0) ?> post<?= ((int)($channel['post_count'] ?? 0)) !== 1 ? 's' : '' ?>
                                    </span>
                                </div>

                                <?php if (!$hasAccess): ?>
                                    <div class="mt-3 flex items-center gap-2 text-sm text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <span>Upgrade to access</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php if ($hasAccess): ?>
                        </a>
                    <?php else: ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
