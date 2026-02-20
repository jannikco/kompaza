<?php
/**
 * Homepage Template: Elegant
 * Split-layout hero (text left, image right), refined typography,
 * card-based content with soft shadows, premium editorial feel.
 */

$primaryColor = $tenant['primary_color'] ?? '#4f46e5';
$secondaryColor = $tenant['secondary_color'] ?? '#0ea5e9';
$heroImage = !empty($tenant['hero_image_path']) ? imageUrl($tenant['hero_image_path']) : '';
?>

<style>
    .elegant-shape {
        background: linear-gradient(135deg, <?= h($primaryColor) ?>20 0%, <?= h($secondaryColor) ?>20 100%);
    }
    .elegant-accent {
        background-color: <?= h($primaryColor) ?>12;
    }
    .elegant-border-accent {
        border-color: <?= h($primaryColor) ?>30;
    }
    .elegant-tag {
        color: <?= h($primaryColor) ?>;
        background-color: <?= h($primaryColor) ?>10;
    }
</style>

<!-- Hero Section -->
<section class="bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
        <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-16">
            <!-- Text Side -->
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-block mb-6">
                    <span class="elegant-tag text-xs font-semibold uppercase tracking-widest px-4 py-1.5 rounded-full"><?= h($companyName) ?></span>
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-5xl xl:text-6xl font-extrabold text-gray-900 leading-[1.1] tracking-tight">
                    <?= h($tenant['tagline'] ?? "Welcome to {$companyName}") ?>
                </h1>
                <p class="mt-6 text-lg text-gray-500 leading-relaxed max-w-xl">
                    <?= h($tenant['hero_subtitle'] ?? 'Explore our content, resources, and products.') ?>
                </p>
                <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <?php if (tenantFeature('orders')): ?>
                        <a href="/produkter" class="btn-brand inline-flex items-center justify-center px-7 py-3.5 text-white font-semibold rounded-lg transition shadow-sm text-base">
                            Browse Products
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if (tenantFeature('blog')): ?>
                        <a href="/blog" class="inline-flex items-center justify-center px-7 py-3.5 bg-white text-gray-700 font-semibold rounded-lg transition text-base border border-gray-200 hover:border-gray-300 hover:bg-gray-50">
                            Read Our Blog
                        </a>
                    <?php elseif (tenantFeature('ebooks')): ?>
                        <a href="/eboger" class="inline-flex items-center justify-center px-7 py-3.5 bg-white text-gray-700 font-semibold rounded-lg transition text-base border border-gray-200 hover:border-gray-300 hover:bg-gray-50">
                            Browse Ebooks
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Image / Shape Side -->
            <div class="flex-shrink-0 w-full lg:w-[45%]">
                <?php if ($heroImage): ?>
                    <div class="relative">
                        <div class="elegant-shape absolute -inset-4 rounded-3xl"></div>
                        <img src="<?= h($heroImage) ?>" alt="<?= h($companyName) ?>"
                             class="relative w-full rounded-2xl shadow-lg">
                    </div>
                <?php else: ?>
                    <div class="elegant-shape rounded-3xl aspect-[4/3] flex items-center justify-center">
                        <?php if (!empty($tenant['logo_path'])): ?>
                            <img src="<?= h(imageUrl($tenant['logo_path'])) ?>" alt="<?= h($companyName) ?>" class="max-w-[200px] max-h-[120px] opacity-60">
                        <?php else: ?>
                            <div class="text-center">
                                <div class="text-5xl font-extrabold text-gray-300"><?= h(mb_substr($companyName, 0, 1)) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($articles)): ?>
<!-- Articles Section -->
<section class="py-16 lg:py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-10">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Latest Articles</h2>
                <p class="mt-2 text-gray-500">Thoughtful insights and expert perspectives</p>
            </div>
            <a href="/blog" class="hidden sm:inline-flex items-center text-sm font-semibold text-brand hover:underline">
                View all
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($articles as $article): ?>
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
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-8 sm:hidden">
            <a href="/blog" class="text-sm font-semibold text-brand hover:underline">View all articles &rarr;</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($ebooks)): ?>
<!-- Ebooks Section -->
<section class="py-16 lg:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-10">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Featured Ebooks</h2>
                <p class="mt-2 text-gray-500">Curated resources for deep learning</p>
            </div>
            <a href="/eboger" class="hidden sm:inline-flex items-center text-sm font-semibold text-brand hover:underline">
                View all
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($ebooks as $ebook): ?>
                <a href="/ebog/<?= h($ebook['slug']) ?>" class="group bg-gray-50 rounded-xl border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-300">
                    <?php if (!empty($ebook['cover_image_path'])): ?>
                        <div class="aspect-[3/4] overflow-hidden bg-gray-100">
                            <img src="<?= h(imageUrl($ebook['cover_image_path'])) ?>" alt="<?= h($ebook['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                    <?php else: ?>
                        <div class="aspect-[3/4] elegant-accent flex items-center justify-center">
                            <svg class="w-14 h-14 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                    <?php endif; ?>
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-gray-900 group-hover:text-brand transition mb-1 leading-snug"><?= h($ebook['title']) ?></h3>
                        <?php if (!empty($ebook['subtitle'])): ?>
                            <p class="text-gray-500 text-sm mb-3 leading-relaxed"><?= h($ebook['subtitle']) ?></p>
                        <?php endif; ?>
                        <?php if ($ebook['price_dkk'] > 0): ?>
                            <span class="text-base font-bold text-gray-900"><?= formatMoney($ebook['price_dkk'], $tenant['currency'] ?? 'DKK') ?></span>
                        <?php else: ?>
                            <span class="text-sm font-semibold text-green-600">Free</span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($courses)): ?>
<!-- Courses Section -->
<section class="py-16 lg:py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-10">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Our Courses</h2>
                <p class="mt-2 text-gray-500">Structured learning paths for every level</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($courses as $course): ?>
                <a href="/kursus/<?= h($course['slug']) ?>" class="group bg-white rounded-xl border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-300">
                    <?php if (!empty($course['cover_image_path'])): ?>
                        <div class="aspect-video overflow-hidden">
                            <img src="<?= h(imageUrl($course['cover_image_path'])) ?>" alt="<?= h($course['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                    <?php else: ?>
                        <div class="aspect-video elegant-accent flex items-center justify-center">
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </div>
                    <?php endif; ?>
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-gray-900 group-hover:text-brand transition mb-2 leading-snug"><?= h($course['title']) ?></h3>
                        <?php if (!empty($course['short_description'])): ?>
                            <p class="text-gray-500 text-sm line-clamp-2 mb-3 leading-relaxed"><?= h($course['short_description']) ?></p>
                        <?php endif; ?>
                        <?php if (($course['price_dkk'] ?? 0) > 0): ?>
                            <span class="text-base font-bold text-gray-900"><?= formatMoney($course['price_dkk'], $tenant['currency'] ?? 'DKK') ?></span>
                        <?php else: ?>
                            <span class="text-sm font-semibold text-green-600">Free</span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($products)): ?>
<!-- Products Section -->
<section class="py-16 lg:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-10">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Our Products</h2>
                <p class="mt-2 text-gray-500">Quality tools built for professionals</p>
            </div>
            <a href="/produkter" class="hidden sm:inline-flex items-center text-sm font-semibold text-brand hover:underline">
                View all
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($products as $product): ?>
                <a href="/produkt/<?= h($product['slug']) ?>" class="group bg-gray-50 rounded-xl border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-300">
                    <?php if (!empty($product['image_path'])): ?>
                        <div class="aspect-video overflow-hidden bg-gray-100">
                            <img src="<?= h(imageUrl($product['image_path'])) ?>" alt="<?= h($product['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                    <?php else: ?>
                        <div class="aspect-video elegant-accent flex items-center justify-center">
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                    <?php endif; ?>
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-gray-900 group-hover:text-brand transition mb-2 leading-snug"><?= h($product['title']) ?></h3>
                        <?php if (!empty($product['short_description'])): ?>
                            <p class="text-gray-500 text-sm line-clamp-2 mb-3 leading-relaxed"><?= h($product['short_description']) ?></p>
                        <?php endif; ?>
                        <span class="text-base font-bold text-gray-900"><?= formatMoney($product['price_dkk'], $tenant['currency'] ?? 'DKK') ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Newsletter Signup -->
<section class="py-16 lg:py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">Stay Updated</h2>
            <p class="text-gray-500 mb-8 leading-relaxed">Subscribe to our newsletter and never miss new content and offers.</p>
            <form action="/newsletter-signup" method="POST" class="max-w-md mx-auto" x-data="{ loading: false, done: false }"
                  @submit.prevent="
                      loading = true;
                      const fd = new FormData($el);
                      fetch('/newsletter-signup', { method: 'POST', body: fd })
                          .then(r => r.json())
                          .then(d => { loading = false; if (d.success) done = true; })
                          .catch(() => loading = false);
                  ">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
                <div x-show="!done" class="flex flex-col sm:flex-row gap-3">
                    <input type="email" name="email" required placeholder="Your email address"
                           class="flex-1 px-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent bg-white">
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
            <p class="mt-4 text-xs text-gray-400">No spam, unsubscribe at any time.</p>
        </div>
    </div>
</section>
