<?php
/**
 * Homepage Section: Courses
 * Receives: $section, $tenant, $template, $courses
 */
if (empty($courses)) return;
$heading = $section['heading'] ?? 'Our Courses';
$subtitle = $section['subtitle'] ?? '';
$count = (int)($section['count'] ?? 3);
$courses = array_slice($courses, 0, $count);
?>
<section class="py-16 lg:py-20 <?= $template === 'elegant' ? 'bg-gray-50' : 'bg-gray-50' ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if ($template === 'starter'): ?>
            <div class="flex items-center justify-between mb-10">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= h($heading) ?></h2>
                <a href="/courses" class="text-sm font-medium text-brand hover:underline">View all &rarr;</a>
            </div>
        <?php elseif ($template === 'bold'): ?>
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900"><?= h($heading) ?></h2>
                <?php if ($subtitle): ?>
                    <p class="mt-3 text-gray-500 text-lg"><?= h($subtitle) ?></p>
                <?php else: ?>
                    <p class="mt-3 text-gray-500 text-lg">Learn from industry experts at your own pace</p>
                <?php endif; ?>
            </div>
        <?php else: /* elegant */ ?>
            <div class="flex items-end justify-between mb-10">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= h($heading) ?></h2>
                    <?php if ($subtitle): ?>
                        <p class="mt-2 text-gray-500"><?= h($subtitle) ?></p>
                    <?php else: ?>
                        <p class="mt-2 text-gray-500">Structured learning paths for every level</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($courses as $course): ?>
                <a href="/course/<?= h($course['slug']) ?>" class="group <?= $template === 'bold' ? 'bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 bold-card' : ($template === 'elegant' ? 'bg-white rounded-xl border border-gray-100 hover:shadow-md transition-all duration-300' : 'bg-white rounded-xl border border-gray-200 hover:shadow-lg transition-shadow duration-300') ?> overflow-hidden">
                    <?php if (!empty($course['cover_image_path'])): ?>
                        <div class="aspect-video overflow-hidden">
                            <img src="<?= h(imageUrl($course['cover_image_path'])) ?>" alt="<?= h($course['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                    <?php else: ?>
                        <div class="aspect-video <?= $template === 'bold' ? 'bold-hero-gradient-light' : ($template === 'elegant' ? 'elegant-accent' : 'bg-gray-100') ?> flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </div>
                    <?php endif; ?>
                    <div class="p-6">
                        <h3 class="<?= $template === 'elegant' ? 'text-base font-semibold leading-snug' : 'text-lg font-bold' ?> text-gray-900 group-hover:text-brand transition mb-2"><?= h($course['title']) ?></h3>
                        <?php if (!empty($course['short_description'])): ?>
                            <p class="text-gray-500 text-sm line-clamp-2 mb-3 <?= $template === 'elegant' ? 'leading-relaxed' : '' ?>"><?= h($course['short_description']) ?></p>
                        <?php endif; ?>
                        <?php if (($course['price_dkk'] ?? 0) > 0): ?>
                            <span class="<?= $template === 'bold' ? 'text-lg font-extrabold' : ($template === 'elegant' ? 'text-base font-bold' : 'text-lg font-bold') ?> text-gray-900"><?= formatMoney($course['price_dkk'], $tenant['currency'] ?? 'DKK') ?></span>
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
    </div>
</section>
