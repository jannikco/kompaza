<?php
$pageTitle = $pageTitle ?? 'Workshop';
$track = $track ?? 'creator-os';
ob_start();
?>
<section class="py-16 max-w-3xl mx-auto px-4">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 text-center">
        <p class="text-xs font-semibold tracking-widest text-orange-600 uppercase mb-3">Unlocked</p>
        <h1 class="text-3xl font-bold text-gray-900 mb-4"><?= h($pageTitle) ?></h1>
        <p class="text-gray-600 mb-8">Thanks for joining. Watch the free session, then continue into the full program when you are ready.</p>
        <div class="aspect-video bg-gray-900 rounded-xl mb-8 flex items-center justify-center text-white text-sm">
            Workshop video — open the full track for curriculum &amp; purchase.
        </div>
        <div class="flex flex-wrap gap-3 justify-center">
            <a href="/<?= h($track) ?>" class="inline-flex px-6 py-3 rounded-lg bg-orange-600 text-white font-semibold hover:bg-orange-700 transition">View <?= h($titles[$track] ?? $track) ?></a>
            <a href="/course/<?= h($track) ?>" class="inline-flex px-6 py-3 rounded-lg bg-gray-100 text-gray-900 font-semibold hover:bg-gray-200 transition">Open course page</a>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
include VIEWS_PATH . '/shop/layout.php';
