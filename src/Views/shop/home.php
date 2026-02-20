<?php
$pageTitle = 'Home';
$tenant = currentTenant();
$companyName = $tenant['company_name'] ?? $tenant['name'] ?? 'Store';
$metaDescription = $tenant['tagline'] ?? "Welcome to {$companyName}";

$articles = [];
if (tenantFeature('blog')) {
    $articles = \App\Models\Article::publishedByTenant($tenant['id'], 3);
}

$ebooks = [];
if (tenantFeature('ebooks')) {
    $ebooks = \App\Models\Ebook::publishedByTenant($tenant['id']);
    $ebooks = array_slice($ebooks, 0, 3);
}

ob_start();
?>

<!-- Hero Section -->
<section class="bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
        <div class="text-center max-w-3xl mx-auto">
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight tracking-tight">
                <?= h($tenant['tagline'] ?? "Welcome to {$companyName}") ?>
            </h1>
            <?php if (!empty($tenant['hero_subtitle'])): ?>
                <p class="mt-6 text-lg sm:text-xl text-gray-500 leading-relaxed">
                    <?= h($tenant['hero_subtitle']) ?>
                </p>
            <?php else: ?>
                <p class="mt-6 text-lg sm:text-xl text-gray-500 leading-relaxed">
                    Explore our content, resources, and products.
                </p>
            <?php endif; ?>
            <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                <?php if (tenantFeature('orders')): ?>
                    <a href="/produkter" class="btn-brand inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-lg transition shadow-sm text-base">
                        Browse Products
                    </a>
                <?php endif; ?>
                <?php if (tenantFeature('blog')): ?>
                    <a href="/blog" class="inline-flex items-center justify-center px-8 py-3.5 bg-gray-100 hover:bg-gray-200 text-gray-900 font-semibold rounded-lg transition text-base">
                        Read Our Blog
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($articles)): ?>
<!-- Recent Articles -->
<section class="py-16 lg:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-10">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Latest Articles</h2>
            <a href="/blog" class="text-sm font-medium text-brand hover:underline">View all &rarr;</a>
        </div>
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
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($ebooks)): ?>
<!-- Featured Ebooks -->
<section class="py-16 lg:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-10">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Featured Ebooks</h2>
            <a href="/eboger" class="text-sm font-medium text-brand hover:underline">View all &rarr;</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($ebooks as $ebook): ?>
                <a href="/ebog/<?= h($ebook['slug']) ?>" class="group bg-gray-50 rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <?php if (!empty($ebook['cover_image_path'])): ?>
                        <div class="aspect-[3/4] overflow-hidden bg-gray-100">
                            <img src="<?= h(imageUrl($ebook['cover_image_path'])) ?>" alt="<?= h($ebook['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                    <?php else: ?>
                        <div class="aspect-[3/4] bg-gray-100 flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                    <?php endif; ?>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-brand transition mb-1"><?= h($ebook['title']) ?></h3>
                        <?php if (!empty($ebook['subtitle'])): ?>
                            <p class="text-gray-500 text-sm mb-3"><?= h($ebook['subtitle']) ?></p>
                        <?php endif; ?>
                        <?php if ($ebook['price_dkk'] > 0): ?>
                            <span class="text-lg font-bold text-gray-900"><?= formatMoney($ebook['price_dkk'], $tenant['currency'] ?? 'DKK') ?></span>
                        <?php else: ?>
                            <span class="text-lg font-bold text-green-600">Free</span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Newsletter Signup -->
<section class="py-16 lg:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl border border-gray-200 p-8 sm:p-12 text-center max-w-2xl mx-auto">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">Stay Updated</h2>
            <p class="text-gray-500 mb-8">Subscribe to our newsletter and never miss new content and offers.</p>
            <form action="/newsletter-signup" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto" x-data="{ loading: false, done: false }"
                  @submit.prevent="
                      loading = true;
                      const fd = new FormData($el);
                      fetch('/newsletter-signup', { method: 'POST', body: fd })
                          .then(r => r.json())
                          .then(d => { loading = false; if (d.success) done = true; })
                          .catch(() => loading = false);
                  ">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
                <div x-show="!done" class="flex flex-col sm:flex-row gap-3 w-full">
                    <input type="email" name="email" required placeholder="Your email address"
                           class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent">
                    <button type="submit" :disabled="loading"
                            class="btn-brand px-6 py-3 text-white font-semibold rounded-lg transition text-sm whitespace-nowrap disabled:opacity-50">
                        <span x-show="!loading">Subscribe</span>
                        <span x-show="loading" x-cloak>Sending...</span>
                    </button>
                </div>
                <div x-show="done" x-cloak class="text-green-600 font-medium py-3">
                    Thank you for subscribing!
                </div>
            </form>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
