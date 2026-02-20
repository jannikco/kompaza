<?php
/**
 * Homepage Template: Bold
 * Full-width gradient hero, modern SaaS aesthetic.
 * Trust strip, featured content cards with hover effects, bold newsletter CTA.
 */

$primaryColor = $tenant['primary_color'] ?? '#4f46e5';
$secondaryColor = $tenant['secondary_color'] ?? '#0ea5e9';
$heroImage = !empty($tenant['hero_image_path']) ? imageUrl($tenant['hero_image_path']) : '';

// Count real data for trust strip
$articleCount = count($articles);
$ebookCount = count($ebooks);
$courseCount = count($courses);
$productCount = count($products);
$totalContent = $articleCount + $ebookCount + $courseCount + $productCount;
?>

<style>
    .bold-hero-gradient {
        background: linear-gradient(135deg, <?= h($primaryColor) ?> 0%, <?= h($secondaryColor) ?> 100%);
    }
    .bold-hero-gradient-light {
        background: linear-gradient(135deg, <?= h($primaryColor) ?>15 0%, <?= h($secondaryColor) ?>15 100%);
    }
    .bold-card:hover {
        transform: translateY(-4px);
    }
    .bold-gradient-text {
        background: linear-gradient(135deg, <?= h($primaryColor) ?>, <?= h($secondaryColor) ?>);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>

<!-- Hero Section -->
<section class="bold-hero-gradient relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 -left-4 w-72 h-72 bg-white rounded-full mix-blend-overlay filter blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-white rounded-full mix-blend-overlay filter blur-3xl"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
        <div class="flex flex-col lg:flex-row items-center gap-12">
            <div class="flex-1 text-center lg:text-left">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight tracking-tight">
                    <?= h($tenant['tagline'] ?? "Welcome to {$companyName}") ?>
                </h1>
                <p class="mt-6 text-lg sm:text-xl text-white/80 leading-relaxed max-w-2xl">
                    <?= h($tenant['hero_subtitle'] ?? 'Explore our content, resources, and products.') ?>
                </p>
                <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <?php if (tenantFeature('orders')): ?>
                        <a href="/produkter" class="inline-flex items-center justify-center px-8 py-4 bg-white text-gray-900 font-bold rounded-xl hover:bg-gray-100 transition shadow-lg text-base">
                            Browse Products
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    <?php endif; ?>
                    <?php if (tenantFeature('blog')): ?>
                        <a href="/blog" class="inline-flex items-center justify-center px-8 py-4 bg-white/10 backdrop-blur text-white font-semibold rounded-xl hover:bg-white/20 transition border border-white/20 text-base">
                            Read Our Blog
                        </a>
                    <?php elseif (tenantFeature('ebooks')): ?>
                        <a href="/eboger" class="inline-flex items-center justify-center px-8 py-4 bg-white/10 backdrop-blur text-white font-semibold rounded-xl hover:bg-white/20 transition border border-white/20 text-base">
                            Browse Ebooks
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($heroImage): ?>
                <div class="flex-shrink-0 lg:w-[45%]">
                    <img src="<?= h($heroImage) ?>" alt="<?= h($companyName) ?>"
                         class="w-full rounded-2xl shadow-2xl">
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if ($totalContent > 0): ?>
<!-- Trust Strip -->
<section class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <?php if ($articleCount > 0): ?>
                <div>
                    <div class="text-3xl font-extrabold bold-gradient-text"><?= $articleCount ?>+</div>
                    <div class="text-sm text-gray-500 mt-1">Articles</div>
                </div>
            <?php endif; ?>
            <?php if ($ebookCount > 0): ?>
                <div>
                    <div class="text-3xl font-extrabold bold-gradient-text"><?= $ebookCount ?>+</div>
                    <div class="text-sm text-gray-500 mt-1">Ebooks</div>
                </div>
            <?php endif; ?>
            <?php if ($courseCount > 0): ?>
                <div>
                    <div class="text-3xl font-extrabold bold-gradient-text"><?= $courseCount ?>+</div>
                    <div class="text-sm text-gray-500 mt-1">Courses</div>
                </div>
            <?php endif; ?>
            <?php if ($productCount > 0): ?>
                <div>
                    <div class="text-3xl font-extrabold bold-gradient-text"><?= $productCount ?>+</div>
                    <div class="text-sm text-gray-500 mt-1">Products</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($articles)): ?>
<!-- Featured Articles -->
<section class="py-16 lg:py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">Latest Articles</h2>
            <p class="mt-3 text-gray-500 text-lg">Insights, guides, and expert knowledge</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($articles as $article): ?>
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
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-10">
            <a href="/blog" class="btn-brand inline-flex items-center px-8 py-3 text-white font-semibold rounded-xl transition shadow-sm text-base">
                View All Articles
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($ebooks)): ?>
<!-- Featured Ebooks -->
<section class="py-16 lg:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">Featured Ebooks</h2>
            <p class="mt-3 text-gray-500 text-lg">In-depth resources to accelerate your growth</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($ebooks as $ebook): ?>
                <a href="/ebog/<?= h($ebook['slug']) ?>" class="group bg-gray-50 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 bold-card">
                    <?php if (!empty($ebook['cover_image_path'])): ?>
                        <div class="aspect-[3/4] overflow-hidden bg-gray-100">
                            <img src="<?= h(imageUrl($ebook['cover_image_path'])) ?>" alt="<?= h($ebook['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                    <?php else: ?>
                        <div class="aspect-[3/4] bold-hero-gradient-light flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                    <?php endif; ?>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-brand transition mb-1"><?= h($ebook['title']) ?></h3>
                        <?php if (!empty($ebook['subtitle'])): ?>
                            <p class="text-gray-500 text-sm mb-3"><?= h($ebook['subtitle']) ?></p>
                        <?php endif; ?>
                        <?php if ($ebook['price_dkk'] > 0): ?>
                            <span class="text-lg font-extrabold text-gray-900"><?= formatMoney($ebook['price_dkk'], $tenant['currency'] ?? 'DKK') ?></span>
                        <?php else: ?>
                            <span class="inline-block text-sm font-bold text-green-600 bg-green-50 px-3 py-1 rounded-full">Free</span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-10">
            <a href="/eboger" class="btn-brand inline-flex items-center px-8 py-3 text-white font-semibold rounded-xl transition shadow-sm text-base">
                View All Ebooks
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($courses)): ?>
<!-- Featured Courses -->
<section class="py-16 lg:py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">Our Courses</h2>
            <p class="mt-3 text-gray-500 text-lg">Learn from industry experts at your own pace</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($courses as $course): ?>
                <a href="/kursus/<?= h($course['slug']) ?>" class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 bold-card">
                    <?php if (!empty($course['cover_image_path'])): ?>
                        <div class="aspect-video overflow-hidden">
                            <img src="<?= h(imageUrl($course['cover_image_path'])) ?>" alt="<?= h($course['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                    <?php else: ?>
                        <div class="aspect-video bold-hero-gradient-light flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </div>
                    <?php endif; ?>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-brand transition mb-2"><?= h($course['title']) ?></h3>
                        <?php if (!empty($course['short_description'])): ?>
                            <p class="text-gray-500 text-sm line-clamp-2 mb-3"><?= h($course['short_description']) ?></p>
                        <?php endif; ?>
                        <?php if (($course['price_dkk'] ?? 0) > 0): ?>
                            <span class="text-lg font-extrabold text-gray-900"><?= formatMoney($course['price_dkk'], $tenant['currency'] ?? 'DKK') ?></span>
                        <?php else: ?>
                            <span class="inline-block text-sm font-bold text-green-600 bg-green-50 px-3 py-1 rounded-full">Free</span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($products)): ?>
<!-- Featured Products -->
<section class="py-16 lg:py-20 <?= !empty($courses) ? 'bg-white' : 'bg-gray-50' ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900">Our Products</h2>
            <p class="mt-3 text-gray-500 text-lg">Premium tools and resources for professionals</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($products as $product): ?>
                <a href="/produkt/<?= h($product['slug']) ?>" class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 bold-card border border-gray-100">
                    <?php if (!empty($product['image_path'])): ?>
                        <div class="aspect-video overflow-hidden">
                            <img src="<?= h(imageUrl($product['image_path'])) ?>" alt="<?= h($product['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                    <?php else: ?>
                        <div class="aspect-video bold-hero-gradient-light flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                    <?php endif; ?>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-brand transition mb-2"><?= h($product['title']) ?></h3>
                        <?php if (!empty($product['short_description'])): ?>
                            <p class="text-gray-500 text-sm line-clamp-2 mb-3"><?= h($product['short_description']) ?></p>
                        <?php endif; ?>
                        <span class="text-lg font-extrabold text-gray-900"><?= formatMoney($product['price_dkk'], $tenant['currency'] ?? 'DKK') ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-10">
            <a href="/produkter" class="btn-brand inline-flex items-center px-8 py-3 text-white font-semibold rounded-xl transition shadow-sm text-base">
                View All Products
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Newsletter Signup -->
<section class="py-16 lg:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bold-hero-gradient rounded-3xl p-8 sm:p-12 lg:p-16 text-center relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full mix-blend-overlay filter blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-80 h-80 bg-white rounded-full mix-blend-overlay filter blur-3xl"></div>
            </div>
            <div class="relative">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white mb-3">Stay Updated</h2>
                <p class="text-white/80 mb-8 text-lg max-w-xl mx-auto">Subscribe to our newsletter and never miss new content and offers.</p>
                <form action="/newsletter-signup" method="POST" class="max-w-lg mx-auto" x-data="{ loading: false, done: false }"
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
                               class="flex-1 px-5 py-4 border-0 rounded-xl text-sm focus:outline-none focus:ring-2 ring-white/50 shadow-lg">
                        <button type="submit" :disabled="loading"
                                class="px-8 py-4 bg-white text-gray-900 font-bold rounded-xl hover:bg-gray-100 transition shadow-lg text-sm whitespace-nowrap disabled:opacity-50">
                            <span x-show="!loading">Subscribe</span>
                            <span x-show="loading" x-cloak>Sending...</span>
                        </button>
                    </div>
                    <div x-show="done" x-cloak class="text-white font-semibold py-4 text-lg">
                        Thank you for subscribing!
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
