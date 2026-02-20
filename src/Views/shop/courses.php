<?php
$pageTitle = 'Courses';
$metaDescription = 'Browse our online courses';
$tenant = $tenant ?? currentTenant();
ob_start();
?>

<section class="py-12 lg:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Our Courses</h1>
            <p class="mt-3 text-lg text-gray-500 max-w-2xl mx-auto">Learn at your own pace with our expert-led online courses.</p>
        </div>

        <?php if (empty($courses)): ?>
            <div class="text-center py-16">
                <p class="text-gray-500">No courses available yet. Check back soon!</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($courses as $course): ?>
                <a href="/course/<?= h($course['slug']) ?>" class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300 group">
                    <div class="aspect-video bg-gray-100 overflow-hidden">
                        <?php if (!empty($course['cover_image_path'])): ?>
                            <img src="<?= h(imageUrl($course['cover_image_path'])) ?>" alt="<?= h($course['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-100 to-purple-100">
                                <svg class="w-12 h-12 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-5">
                        <?php if ($course['is_featured']): ?>
                            <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded bg-yellow-100 text-yellow-800 mb-2">Featured</span>
                        <?php endif; ?>
                        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-brand transition"><?= h($course['title']) ?></h3>
                        <?php if (!empty($course['short_description'])): ?>
                            <p class="mt-2 text-sm text-gray-500 line-clamp-2"><?= h($course['short_description']) ?></p>
                        <?php endif; ?>
                        <div class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-400">
                                <?= (int)$course['total_lessons'] ?> lessons
                                <?php if ($course['total_duration_seconds']): ?>
                                    &middot; <?= gmdate('G\h i\m', $course['total_duration_seconds']) ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <?php if ($course['pricing_type'] === 'free'): ?>
                                    <span class="text-sm font-bold text-green-600">Free</span>
                                <?php elseif ($course['pricing_type'] === 'one_time'): ?>
                                    <span class="text-sm font-bold text-gray-900"><?= formatMoney($course['price_dkk']) ?></span>
                                    <?php if (!empty($course['compare_price_dkk']) && $course['compare_price_dkk'] > $course['price_dkk']): ?>
                                        <span class="text-xs text-gray-400 line-through ml-1"><?= formatMoney($course['compare_price_dkk']) ?></span>
                                    <?php endif; ?>
                                <?php elseif ($course['pricing_type'] === 'subscription'): ?>
                                    <span class="text-sm font-bold text-gray-900">From <?= formatMoney($course['subscription_price_monthly_dkk']) ?>/mo</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($comingSoon)): ?>
            <div class="mt-16">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Coming Soon</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($comingSoon as $course): ?>
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden relative">
                        <div class="absolute top-3 right-3 z-10">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Coming Soon</span>
                        </div>
                        <div class="aspect-video bg-gray-100 overflow-hidden opacity-75">
                            <?php if (!empty($course['cover_image_path'])): ?>
                                <img src="<?= h(imageUrl($course['cover_image_path'])) ?>" alt="<?= h($course['title']) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-100 to-purple-100">
                                    <svg class="w-12 h-12 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-5">
                            <h3 class="text-lg font-semibold text-gray-900"><?= h($course['title']) ?></h3>
                            <?php if (!empty($course['short_description'])): ?>
                                <p class="mt-2 text-sm text-gray-500 line-clamp-2"><?= h($course['short_description']) ?></p>
                            <?php endif; ?>
                            <form method="POST" action="/course/waitlist" class="mt-4" x-data="{ showForm: false }">
                                <?= csrfField() ?>
                                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                <template x-if="!showForm">
                                    <button type="button" @click="showForm = true" class="w-full py-2.5 px-4 text-sm font-medium text-white rounded-lg transition" style="background-color: var(--brand-color, #3b82f6);">
                                        Join Waitlist
                                    </button>
                                </template>
                                <template x-if="showForm">
                                    <div class="space-y-2">
                                        <input type="email" name="email" required placeholder="Your email" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-transparent">
                                        <input type="text" name="name" placeholder="Your name (optional)" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-transparent">
                                        <button type="submit" class="w-full py-2.5 px-4 text-sm font-medium text-white rounded-lg transition" style="background-color: var(--brand-color, #3b82f6);">
                                            Notify Me
                                        </button>
                                    </div>
                                </template>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
