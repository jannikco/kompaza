<?php
$pageTitle = $program['title'];
$metaDescription = $program['short_description'] ?? '';
$tenant = $tenant ?? currentTenant();
ob_start();
?>

<section class="py-12 lg:py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Hero -->
        <div class="text-center mb-12">
            <?php if (!empty($program['cover_image_path'])): ?>
            <div class="aspect-video max-w-3xl mx-auto rounded-xl overflow-hidden mb-8">
                <img src="<?= h(imageUrl($program['cover_image_path'])) ?>" alt="<?= h($program['title']) ?>" class="w-full h-full object-cover">
            </div>
            <?php endif; ?>

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 leading-tight"><?= h($program['title']) ?></h1>

            <?php if (!empty($program['short_description'])): ?>
            <p class="mt-4 text-lg text-gray-500 max-w-2xl mx-auto"><?= h($program['short_description']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Description -->
        <?php if (!empty($program['description'])): ?>
        <div class="prose prose-lg prose-gray max-w-none mb-16">
            <?= $program['description'] ?>
        </div>
        <?php endif; ?>

        <!-- Tiers -->
        <?php if (!empty($tiers)): ?>
        <div class="mb-16">
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-8">Choose Your Tier</h2>
            <div class="grid grid-cols-1 <?= count($tiers) >= 3 ? 'md:grid-cols-3' : (count($tiers) === 2 ? 'md:grid-cols-2' : 'max-w-md mx-auto') ?> gap-6">
                <?php foreach ($tiers as $index => $tier): ?>
                <div class="relative bg-white rounded-2xl border <?= $index === 1 && count($tiers) >= 3 ? 'border-brand ring-2 ring-brand shadow-xl scale-105' : 'border-gray-200 shadow-sm hover:shadow-md' ?> transition-shadow p-6 flex flex-col">
                    <?php if ($index === 1 && count($tiers) >= 3): ?>
                    <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand text-white">Most Popular</span>
                    </div>
                    <?php endif; ?>

                    <div class="text-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900"><?= h($tier['name']) ?></h3>
                        <?php if (!empty($tier['description'])): ?>
                        <p class="text-sm text-gray-500 mt-1"><?= h($tier['description']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Pricing -->
                    <div class="text-center mb-6">
                        <?php if ($tier['upfront_price_dkk'] > 0): ?>
                        <div class="text-3xl font-bold text-gray-900"><?= number_format((float)$tier['upfront_price_dkk'], 0, ',', '.') ?> <span class="text-base font-normal text-gray-500">DKK</span></div>
                        <p class="text-sm text-gray-400">one-time payment</p>
                        <?php endif; ?>
                        <?php if ($tier['monthly_price_dkk'] > 0): ?>
                        <div class="<?= $tier['upfront_price_dkk'] > 0 ? 'mt-2' : '' ?>">
                            <span class="text-2xl font-bold text-gray-900"><?= number_format((float)$tier['monthly_price_dkk'], 0, ',', '.') ?></span>
                            <span class="text-base text-gray-500">DKK/month</span>
                        </div>
                        <?php endif; ?>
                        <?php if ($tier['upfront_price_dkk'] <= 0 && $tier['monthly_price_dkk'] <= 0): ?>
                        <div class="text-3xl font-bold text-gray-900">Free</div>
                        <?php endif; ?>
                    </div>

                    <!-- Features -->
                    <?php if (!empty($tier['features'])): ?>
                    <ul class="space-y-3 mb-8 flex-1">
                        <?php foreach (explode("\n", $tier['features']) as $feature): ?>
                            <?php if (trim($feature)): ?>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-sm text-gray-600"><?= h(trim($feature)) ?></span>
                            </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <div class="flex-1"></div>
                    <?php endif; ?>

                    <!-- CTA -->
                    <div class="mt-auto">
                        <?php if ($tier['max_members']): ?>
                        <p class="text-xs text-gray-400 text-center mb-3">Limited to <?= (int)$tier['max_members'] ?> members</p>
                        <?php endif; ?>
                        <a href="/login?redirect=<?= urlencode('/mastermind/' . $program['slug']) ?>"
                           class="block w-full text-center px-6 py-3 rounded-xl font-semibold text-sm transition <?= ($index === 1 && count($tiers) >= 3) ? 'btn-brand text-white' : 'bg-gray-100 text-gray-900 hover:bg-gray-200' ?>">
                            Enroll Now
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
