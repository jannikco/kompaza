<section class="py-20 bg-gray-50">
    <div class="max-w-lg mx-auto px-4 text-center">
        <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Tak for dit køb!</h1>

        <?php if ($ebook): ?>
            <p class="text-gray-600 mb-6">
                Du har købt <strong><?= h($ebook['title']) ?></strong>.
            </p>
        <?php endif; ?>

        <?php if ($downloadUrl): ?>
            <a href="<?= h($downloadUrl) ?>"
                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download e-bog
            </a>
            <p class="text-sm text-gray-500 mt-4">
                Download-linket er gyldigt i 7 dage med op til 5 downloads.
            </p>
        <?php else: ?>
            <p class="text-gray-600 mb-6">
                Din betaling behandles. Du vil modtage et download-link snart.
            </p>
        <?php endif; ?>

        <div class="mt-8">
            <a href="/" class="text-blue-600 hover:text-blue-700">&larr; Tilbage til forsiden</a>
        </div>
    </div>
</section>
