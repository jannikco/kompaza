<?php
/**
 * Homepage Section: Articles
 * Receives: $section, $tenant, $template, $articles
 */
if (empty($articles)) return;
$heading = $section['heading'] ?? 'Latest Articles';
$subtitle = $section['subtitle'] ?? '';
$count = (int)($section['count'] ?? 3);
$articles = array_slice($articles, 0, $count);
?>
<section class="py-16 lg:py-20 <?= $template === 'starter' ? '' : ($template === 'bold' ? 'bg-gray-50' : 'bg-gray-50') ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if ($template === 'starter'): ?>
            <div class="flex items-center justify-between mb-10">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= h($heading) ?></h2>
                <a href="/blog" class="text-sm font-medium text-brand hover:underline">View all &rarr;</a>
            </div>
        <?php elseif ($template === 'bold'): ?>
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900"><?= h($heading) ?></h2>
                <?php if ($subtitle): ?>
                    <p class="mt-3 text-gray-500 text-lg"><?= h($subtitle) ?></p>
                <?php else: ?>
                    <p class="mt-3 text-gray-500 text-lg">Insights, guides, and expert knowledge</p>
                <?php endif; ?>
            </div>
        <?php else: /* elegant */ ?>
            <div class="flex items-end justify-between mb-10">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= h($heading) ?></h2>
                    <?php if ($subtitle): ?>
                        <p class="mt-2 text-gray-500"><?= h($subtitle) ?></p>
                    <?php else: ?>
                        <p class="mt-2 text-gray-500">Thoughtful insights and expert perspectives</p>
                    <?php endif; ?>
                </div>
                <a href="/blog" class="hidden sm:inline-flex items-center text-sm font-semibold text-brand hover:underline">
                    View all
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
        <?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($articles as $article): ?>
                <?php if ($template === 'bold'): ?>
                    <a href="/blog/<?= h($article['slug']) ?>" class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 bold-card">
                        <?php if (!empty($article['featured_image'])): ?>
                            <div class="aspect-video overflow-hidden">
                                <img src="<?= h(imageUrl($article['featured_image'])) ?>" alt="<?= h($article['title']) ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </div>
                        <?php else: ?>
                            <div class="aspect-video bold-hero-gradient-light flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                            </div>
                        <?php endif; ?>
                        <div class="p-6">
                            <?php if (!empty($article['category'])): ?>
                                <span class="inline-block text-xs font-bold text-brand uppercase tracking-wider mb-3 px-3 py-1 rounded-full bg-brand/10"><?= h($article['category']) ?></span>
                            <?php endif; ?>
                            <h3 class="text-lg font-bold text-gray-900 group-hover:text-brand transition mb-2 line-clamp-2"><?= h($article['title']) ?></h3>
                            <?php if (!empty($article['excerpt'])): ?>
                                <p class="text-gray-500 text-sm line-clamp-2"><?= h($article['excerpt']) ?></p>
                            <?php endif; ?>
                            <div class="mt-4 flex items-center text-sm text-brand font-semibold">
                                Read more
                                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </div>
                        </div>
                    </a>
                <?php elseif ($template === 'elegant'): ?>
                    <a href="/blog/<?= h($article['slug']) ?>" class="group bg-white rounded-xl border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-300">
                        <?php if (!empty($article['featured_image'])): ?>
                            <div class="aspect-video overflow-hidden">
                                <img src="<?= h(imageUrl($article['featured_image'])) ?>" alt="<?= h($article['title']) ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </div>
                        <?php else: ?>
                            <div class="aspect-video elegant-accent flex items-center justify-center">
                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                            </div>
                        <?php endif; ?>
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-3">
                                <?php if (!empty($article['category'])): ?>
                                    <span class="elegant-tag text-xs font-semibold uppercase tracking-wider px-2.5 py-0.5 rounded-full"><?= h($article['category']) ?></span>
                                <?php endif; ?>
                                <span class="text-xs text-gray-400"><?= formatDate($article['published_at'] ?? $article['created_at']) ?></span>
                            </div>
                            <h3 class="text-base font-semibold text-gray-900 group-hover:text-brand transition leading-snug line-clamp-2"><?= h($article['title']) ?></h3>
                            <?php if (!empty($article['excerpt'])): ?>
                                <p class="mt-2 text-gray-500 text-sm line-clamp-2 leading-relaxed"><?= h($article['excerpt']) ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php else: /* starter */ ?>
                    <a href="/blog/<?= h($article['slug']) ?>" class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <?php if (!empty($article['featured_image'])): ?>
                            <div class="aspect-video overflow-hidden">
                                <img src="<?= h(imageUrl($article['featured_image'])) ?>" alt="<?= h($article['title']) ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                        <?php else: ?>
                            <div class="aspect-video bg-gray-100 flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                            </div>
                        <?php endif; ?>
                        <div class="p-6">
                            <?php if (!empty($article['category'])): ?>
                                <span class="inline-block text-xs font-semibold text-brand uppercase tracking-wider mb-2"><?= h($article['category']) ?></span>
                            <?php endif; ?>
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-brand transition mb-2 line-clamp-2"><?= h($article['title']) ?></h3>
                            <?php if (!empty($article['excerpt'])): ?>
                                <p class="text-gray-500 text-sm line-clamp-2"><?= h($article['excerpt']) ?></p>
                            <?php endif; ?>
                            <p class="mt-4 text-xs text-gray-400"><?= formatDate($article['published_at'] ?? $article['created_at']) ?></p>
                        </div>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php if ($template === 'bold'): ?>
            <div class="text-center mt-10">
                <a href="/blog" class="btn-brand inline-flex items-center px-8 py-3 text-white font-semibold rounded-xl transition shadow-sm text-base">
                    View All Articles
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
        <?php elseif ($template === 'elegant'): ?>
            <div class="text-center mt-8 sm:hidden">
                <a href="/blog" class="text-sm font-semibold text-brand hover:underline">View all articles &rarr;</a>
            </div>
        <?php endif; ?>
    </div>
</section>
