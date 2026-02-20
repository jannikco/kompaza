<?php
$pageTitle = 'Blog';
$tenant = currentTenant();
$metaDescription = "Read the latest articles from " . h($tenant['company_name'] ?? $tenant['name'] ?? 'our blog');

// $articles should be passed from the controller
$articles = $articles ?? [];

ob_start();
?>

<section class="py-12 lg:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="text-center mb-12">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Blog</h1>
            <p class="mt-3 text-lg text-gray-500 max-w-2xl mx-auto">Insights, guides, and updates from our team.</p>
        </div>

        <?php if (empty($articles)): ?>
            <div class="text-center py-16">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                <p class="text-gray-500 text-lg">No articles published yet.</p>
                <p class="text-gray-400 text-sm mt-1">Check back soon for new content.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($articles as $article): ?>
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
                            <div class="flex items-center gap-3 mb-3">
                                <?php if (!empty($article['category'])): ?>
                                    <span class="inline-block text-xs font-semibold text-brand uppercase tracking-wider"><?= h($article['category']) ?></span>
                                <?php endif; ?>
                                <span class="text-xs text-gray-400"><?= formatDate($article['published_at'] ?? $article['created_at']) ?></span>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900 group-hover:text-brand transition mb-2 line-clamp-2"><?= h($article['title']) ?></h2>
                            <?php if (!empty($article['excerpt'])): ?>
                                <p class="text-gray-500 text-sm line-clamp-3"><?= h($article['excerpt']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($article['author_name'])): ?>
                                <p class="mt-4 text-xs text-gray-400">By <?= h($article['author_name']) ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
