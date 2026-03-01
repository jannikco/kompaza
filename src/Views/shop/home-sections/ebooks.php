<?php
/**
 * Homepage Section: Ebooks
 * Receives: $section, $tenant, $template, $ebooks
 */
if (empty($ebooks)) return;
$heading = $section['heading'] ?? 'Featured Ebooks';
$subtitle = $section['subtitle'] ?? '';
$count = (int)($section['count'] ?? 3);
$ebooks = array_slice($ebooks, 0, $count);
?>
<section class="py-16 lg:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if ($template === 'starter'): ?>
            <div class="flex items-center justify-between mb-10">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= h($heading) ?></h2>
                <a href="/eboger" class="text-sm font-medium text-brand hover:underline">View all &rarr;</a>
            </div>
        <?php elseif ($template === 'bold'): ?>
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900"><?= h($heading) ?></h2>
                <?php if ($subtitle): ?>
                    <p class="mt-3 text-gray-500 text-lg"><?= h($subtitle) ?></p>
                <?php else: ?>
                    <p class="mt-3 text-gray-500 text-lg">In-depth resources to accelerate your growth</p>
                <?php endif; ?>
            </div>
        <?php else: /* elegant */ ?>
            <div class="flex items-end justify-between mb-10">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= h($heading) ?></h2>
                    <?php if ($subtitle): ?>
                        <p class="mt-2 text-gray-500"><?= h($subtitle) ?></p>
                    <?php else: ?>
                        <p class="mt-2 text-gray-500">Curated resources for deep learning</p>
                    <?php endif; ?>
                </div>
                <a href="/eboger" class="hidden sm:inline-flex items-center text-sm font-semibold text-brand hover:underline">
                    View all
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
        <?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($ebooks as $ebook): ?>
                <a href="/ebog/<?= h($ebook['slug']) ?>" class="group <?= $template === 'bold' ? 'bg-gray-50 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 bold-card' : ($template === 'elegant' ? 'bg-gray-50 rounded-xl border border-gray-100 hover:shadow-md transition-all duration-300' : 'bg-gray-50 rounded-xl border border-gray-200 hover:shadow-lg transition-shadow duration-300') ?> overflow-hidden">
                    <?php if (!empty($ebook['cover_image_path'])): ?>
                        <div class="aspect-[3/4] overflow-hidden bg-gray-100">
                            <img src="<?= h(imageUrl($ebook['cover_image_path'])) ?>" alt="<?= h($ebook['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-<?= $template === 'starter' ? '300' : '500' ?>">
                        </div>
                    <?php else: ?>
                        <div class="aspect-[3/4] <?= $template === 'bold' ? 'bold-hero-gradient-light' : ($template === 'elegant' ? 'elegant-accent' : 'bg-gray-100') ?> flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                    <?php endif; ?>
                    <div class="p-6">
                        <h3 class="<?= $template === 'elegant' ? 'text-base font-semibold' : 'text-lg font-' . ($template === 'bold' ? 'bold' : 'semibold') ?> text-gray-900 group-hover:text-brand transition mb-1 <?= $template === 'elegant' ? 'leading-snug' : '' ?>"><?= h($ebook['title']) ?></h3>
                        <?php if (!empty($ebook['subtitle'])): ?>
                            <p class="text-gray-500 text-sm mb-3 <?= $template === 'elegant' ? 'leading-relaxed' : '' ?>"><?= h($ebook['subtitle']) ?></p>
                        <?php endif; ?>
                        <?php if ($ebook['price_dkk'] > 0): ?>
                            <span class="<?= $template === 'bold' ? 'text-lg font-extrabold' : ($template === 'elegant' ? 'text-base font-bold' : 'text-lg font-bold') ?> text-gray-900"><?= formatMoney($ebook['price_dkk'], $tenant['currency'] ?? 'DKK') ?></span>
                        <?php else: ?>
                            <?php if ($template === 'bold'): ?>
                                <span class="inline-block text-sm font-bold text-green-600 bg-green-50 px-3 py-1 rounded-full">Free</span>
                            <?php elseif ($template === 'elegant'): ?>
                                <span class="text-sm font-semibold text-green-600">Free</span>
                            <?php else: ?>
                                <span class="text-lg font-bold text-green-600">Free</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <?php if ($template === 'bold'): ?>
            <div class="text-center mt-10">
                <a href="/eboger" class="btn-brand inline-flex items-center px-8 py-3 text-white font-semibold rounded-xl transition shadow-sm text-base">
                    View All Ebooks
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
