<?php
$pageTitle = h($webinar['title']);
$metaDescription = h($webinar['description'] ?? $webinar['registration_subheadline'] ?? '');

ob_start();
?>

<section class="py-12 lg:py-20 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">

        <?php if ($showRoom): ?>
        <!-- Webinar Room -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900"><?= h($webinar['title']) ?></h1>
            <?php if ($webinar['host_name']): ?>
            <p class="text-gray-500 mt-2">Hosted by <?= h($webinar['host_name']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Video embed -->
        <div class="bg-black rounded-xl overflow-hidden mb-8 aspect-video">
            <?php
            $videoUrl = $webinar['status'] === 'replay' && $webinar['replay_url'] ? $webinar['replay_url'] : $webinar['embed_url'];
            if ($videoUrl):
                // Convert YouTube watch URLs to embed
                $embedUrl = $videoUrl;
                if (str_contains($videoUrl, 'youtube.com/watch')) {
                    parse_str(parse_url($videoUrl, PHP_URL_QUERY), $ytParams);
                    $embedUrl = 'https://www.youtube.com/embed/' . ($ytParams['v'] ?? '');
                } elseif (str_contains($videoUrl, 'youtu.be/')) {
                    $embedUrl = 'https://www.youtube.com/embed/' . basename(parse_url($videoUrl, PHP_URL_PATH));
                }
            ?>
            <iframe src="<?= h($embedUrl) ?>" class="w-full h-full" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            <?php else: ?>
            <div class="w-full h-full flex items-center justify-center text-white">
                <p class="text-lg"><?= $webinar['status'] === 'live' ? 'The webinar will begin shortly...' : 'Replay coming soon...' ?></p>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($webinar['status'] === 'replay'): ?>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8 text-center">
            <p class="text-blue-700 text-sm font-medium">You are watching the replay of this webinar.</p>
        </div>
        <?php endif; ?>

        <!-- Post-webinar offer -->
        <?php if ($webinar['offer_product_id'] && $webinar['offer_headline']): ?>
        <div class="bg-white rounded-xl border-2 border-indigo-500 p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-3"><?= h($webinar['offer_headline']) ?></h2>
            <?php if ($webinar['offer_description']): ?>
            <p class="text-gray-600 mb-6"><?= h($webinar['offer_description']) ?></p>
            <?php endif; ?>
            <a href="/buy/<?= h($webinar['offer_product_id']) ?>" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition">
                Get the Offer
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <!-- Registration Page -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left: Info -->
            <div>
                <?php if ($webinar['registration_image_path']): ?>
                <img src="<?= h($webinar['registration_image_path']) ?>" alt="<?= h($webinar['title']) ?>" class="rounded-xl mb-6 w-full">
                <?php endif; ?>

                <h1 class="text-3xl font-bold text-gray-900 mb-4">
                    <?= h($webinar['registration_headline'] ?: $webinar['title']) ?>
                </h1>

                <?php if ($webinar['registration_subheadline']): ?>
                <p class="text-lg text-gray-600 mb-6"><?= h($webinar['registration_subheadline']) ?></p>
                <?php endif; ?>

                <?php if (!empty($bulletPoints)): ?>
                <ul class="space-y-3 mb-6">
                    <?php foreach ($bulletPoints as $bp): ?>
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-gray-700"><?= h($bp) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <?php if ($webinar['scheduled_at']): ?>
                <div class="bg-indigo-50 rounded-lg p-4 mb-6">
                    <p class="text-indigo-700 font-semibold">
                        <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <?= date('l, F j, Y \a\t g:i A', strtotime($webinar['scheduled_at'])) ?>
                    </p>
                    <p class="text-sm text-indigo-600 mt-1"><?= (int)$webinar['duration_minutes'] ?> minutes</p>
                </div>
                <?php endif; ?>

                <?php if ($webinar['host_name']): ?>
                <div class="flex items-center gap-4 mt-6">
                    <?php if ($webinar['host_image_path']): ?>
                    <img src="<?= h($webinar['host_image_path']) ?>" alt="<?= h($webinar['host_name']) ?>" class="w-12 h-12 rounded-full object-cover">
                    <?php endif; ?>
                    <div>
                        <p class="font-medium text-gray-900"><?= h($webinar['host_name']) ?></p>
                        <?php if ($webinar['host_bio']): ?>
                        <p class="text-sm text-gray-500"><?= h($webinar['host_bio']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right: Registration form -->
            <div>
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm sticky top-24">
                    <?php if ($alreadyRegistered): ?>
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">You're Registered!</h3>
                        <p class="text-gray-500">Check your email for details and reminders.</p>
                    </div>
                    <?php else: ?>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Register for Free</h3>
                    <form method="POST" action="/webinar/register">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="webinar_id" value="<?= $webinar['id'] ?>">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
                                <input type="text" name="name" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="John Doe">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" name="email" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="john@example.com">
                            </div>
                            <button type="submit" class="w-full px-6 py-3.5 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition text-lg">
                                <?= h($webinar['registration_cta_text'] ?: 'Register Now') ?>
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 text-center mt-3">Free registration. No credit card required.</p>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
