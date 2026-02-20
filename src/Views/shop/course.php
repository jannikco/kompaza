<?php
$pageTitle = $course['title'];
$metaDescription = $course['short_description'] ?? '';
$tenant = $tenant ?? currentTenant();
ob_start();
?>

<section class="py-12 lg:py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Main content -->
            <div class="lg:col-span-2">
                <?php if (!empty($course['cover_image_path'])): ?>
                    <div class="aspect-video rounded-xl overflow-hidden mb-8">
                        <img src="<?= h(imageUrl($course['cover_image_path'])) ?>" alt="<?= h($course['title']) ?>" class="w-full h-full object-cover">
                    </div>
                <?php endif; ?>

                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900"><?= h($course['title']) ?></h1>
                <?php if (!empty($course['subtitle'])): ?>
                    <p class="mt-2 text-lg text-gray-500"><?= h($course['subtitle']) ?></p>
                <?php endif; ?>

                <div class="flex items-center space-x-4 mt-4 text-sm text-gray-400">
                    <span><?= (int)$course['total_lessons'] ?> lessons</span>
                    <?php if ($course['total_duration_seconds']): ?>
                        <span>&middot;</span>
                        <span><?= gmdate('G\h i\m', $course['total_duration_seconds']) ?> total</span>
                    <?php endif; ?>
                    <span>&middot;</span>
                    <span><?= (int)$course['enrollment_count'] ?> students</span>
                </div>

                <!-- Instructor -->
                <?php if (!empty($course['instructor_name'])): ?>
                <div class="flex items-center space-x-3 mt-6 p-4 bg-gray-50 rounded-xl">
                    <?php if (!empty($course['instructor_image_path'])): ?>
                        <img src="<?= h(imageUrl($course['instructor_image_path'])) ?>" class="w-12 h-12 rounded-full object-cover">
                    <?php else: ?>
                        <div class="w-12 h-12 rounded-full bg-brand/10 flex items-center justify-center">
                            <span class="text-brand font-bold text-lg"><?= h(mb_substr($course['instructor_name'], 0, 1)) ?></span>
                        </div>
                    <?php endif; ?>
                    <div>
                        <p class="font-semibold text-gray-900"><?= h($course['instructor_name']) ?></p>
                        <?php if (!empty($course['instructor_bio'])): ?>
                            <p class="text-sm text-gray-500"><?= h($course['instructor_bio']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Description -->
                <?php if (!empty($course['description'])): ?>
                <div class="mt-8 prose prose-gray max-w-none">
                    <?= $course['description'] ?>
                </div>
                <?php endif; ?>

                <!-- Curriculum -->
                <div class="mt-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Curriculum</h2>
                    <div class="space-y-3">
                        <?php foreach ($modules as $module): ?>
                        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden" x-data="{ open: false }">
                            <button @click="open = !open" class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 transition">
                                <div>
                                    <h3 class="font-semibold text-gray-900"><?= h($module['title']) ?></h3>
                                    <p class="text-sm text-gray-400 mt-0.5"><?= count($module['lessons']) ?> lessons</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-cloak class="border-t border-gray-100 divide-y divide-gray-50">
                                <?php foreach ($module['lessons'] as $lesson): ?>
                                <div class="flex items-center justify-between px-5 py-3">
                                    <div class="flex items-center space-x-3">
                                        <?php if ($lesson['lesson_type'] === 'video' || $lesson['lesson_type'] === 'video_text'): ?>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <?php else: ?>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <?php endif; ?>
                                        <span class="text-sm text-gray-700"><?= h($lesson['title']) ?></span>
                                        <?php if ($lesson['is_preview']): ?>
                                            <span class="text-xs text-brand font-medium">Preview</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($lesson['video_duration_seconds']): ?>
                                        <span class="text-xs text-gray-400"><?= gmdate('i:s', $lesson['video_duration_seconds']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar: pricing card -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <?php if ($enrollment): ?>
                        <!-- Already enrolled -->
                        <div class="text-center">
                            <div class="mb-4">
                                <div class="w-16 bg-gray-200 rounded-full h-2 mx-auto">
                                    <div class="bg-brand h-2 rounded-full" style="width: <?= (float)$enrollment['progress_percent'] ?>%"></div>
                                </div>
                                <p class="text-sm text-gray-500 mt-2"><?= (int)$enrollment['progress_percent'] ?>% complete</p>
                            </div>
                            <a href="/course/<?= h($course['slug']) ?>/learn" class="block w-full btn-brand text-white font-semibold py-3 px-6 rounded-xl text-center transition">
                                Continue Learning
                            </a>
                        </div>
                    <?php elseif ($course['pricing_type'] === 'free'): ?>
                        <div class="text-center mb-4">
                            <span class="text-3xl font-bold text-green-600">Free</span>
                        </div>
                        <form method="POST" action="/course/<?= h($course['slug']) ?>/enroll-free">
                            <?= csrfField() ?>
                            <button type="submit" class="block w-full btn-brand text-white font-semibold py-3 px-6 rounded-xl text-center transition">
                                Enroll for Free
                            </button>
                        </form>
                    <?php elseif ($course['pricing_type'] === 'one_time'): ?>
                        <div class="text-center mb-4">
                            <?php if (!empty($course['compare_price_dkk']) && $course['compare_price_dkk'] > $course['price_dkk']): ?>
                                <span class="text-lg text-gray-400 line-through"><?= formatMoney($course['compare_price_dkk']) ?></span>
                            <?php endif; ?>
                            <div class="text-3xl font-bold text-gray-900"><?= formatMoney($course['price_dkk']) ?></div>
                        </div>
                        <form method="POST" action="/course/<?= h($course['slug']) ?>/buy">
                            <?= csrfField() ?>
                            <button type="submit" class="block w-full btn-brand text-white font-semibold py-3 px-6 rounded-xl text-center transition">
                                Buy Now
                            </button>
                        </form>
                        <p class="text-xs text-gray-400 text-center mt-3">Lifetime access. One-time payment.</p>
                    <?php elseif ($course['pricing_type'] === 'subscription'): ?>
                        <div x-data="{ plan: 'monthly' }" class="space-y-4">
                            <div class="grid grid-cols-2 gap-2">
                                <button @click="plan = 'monthly'" :class="plan === 'monthly' ? 'bg-brand text-white' : 'bg-gray-100 text-gray-700'" class="py-2 px-3 rounded-lg text-sm font-medium transition">Monthly</button>
                                <button @click="plan = 'yearly'" :class="plan === 'yearly' ? 'bg-brand text-white' : 'bg-gray-100 text-gray-700'" class="py-2 px-3 rounded-lg text-sm font-medium transition">Yearly</button>
                            </div>
                            <div class="text-center">
                                <div x-show="plan === 'monthly'" class="text-3xl font-bold text-gray-900"><?= formatMoney($course['subscription_price_monthly_dkk']) ?><span class="text-base font-normal text-gray-400">/mo</span></div>
                                <div x-show="plan === 'yearly'" x-cloak class="text-3xl font-bold text-gray-900"><?= formatMoney($course['subscription_price_yearly_dkk']) ?><span class="text-base font-normal text-gray-400">/yr</span></div>
                            </div>
                            <form method="POST" action="/course/<?= h($course['slug']) ?>/subscribe">
                                <?= csrfField() ?>
                                <input type="hidden" name="plan" :value="plan">
                                <button type="submit" class="block w-full btn-brand text-white font-semibold py-3 px-6 rounded-xl text-center transition">
                                    Subscribe Now
                                </button>
                            </form>
                            <p class="text-xs text-gray-400 text-center">Cancel anytime.</p>
                        </div>
                    <?php endif; ?>

                    <?php if (!isAuthenticated()): ?>
                        <p class="text-xs text-gray-400 text-center mt-4">
                            <a href="/login" class="text-brand hover:underline">Log in</a> or <a href="/registrer" class="text-brand hover:underline">create an account</a> to enroll.
                        </p>
                    <?php endif; ?>

                    <!-- Course stats -->
                    <div class="mt-6 pt-6 border-t border-gray-100 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Lessons</span>
                            <span class="font-medium text-gray-900"><?= (int)$course['total_lessons'] ?></span>
                        </div>
                        <?php if ($course['total_duration_seconds']): ?>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Duration</span>
                            <span class="font-medium text-gray-900"><?= gmdate('G\h i\m', $course['total_duration_seconds']) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Students</span>
                            <span class="font-medium text-gray-900"><?= (int)$course['enrollment_count'] ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
