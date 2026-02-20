<!-- Hero -->
<section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-900 via-blue-900 to-gray-900"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">
                    <?= h($ebook['hero_headline'] ?? $ebook['title']) ?>
                </h1>
                <?php if ($ebook['hero_subheadline'] ?? $ebook['subtitle']): ?>
                    <p class="text-xl text-gray-300 mb-6"><?= h($ebook['hero_subheadline'] ?? $ebook['subtitle']) ?></p>
                <?php endif; ?>
                <div class="flex items-center space-x-6 mb-8">
                    <span class="text-2xl font-bold <?= $ebook['price_dkk'] > 0 ? 'text-amber-400' : 'text-green-400' ?>">
                        <?= $ebook['price_dkk'] > 0 ? number_format($ebook['price_dkk'], 2, ',', '.') . ' DKK' : 'Gratis' ?>
                    </span>
                    <?php if ($ebook['page_count']): ?>
                        <span class="text-gray-400"><?= $ebook['page_count'] ?> sider</span>
                    <?php endif; ?>
                </div>
                <?php if ($ebook['price_dkk'] > 0): ?>
                <div x-data="{ loading: false }">
                    <button @click="
                        loading = true;
                        fetch('/api/ebook-checkout', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({ ebook_id: <?= (int)$ebook['id'] ?> })
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.checkout_url) {
                                window.location.href = data.checkout_url;
                            } else {
                                alert(data.error || 'Der opstod en fejl.');
                                loading = false;
                            }
                        })
                        .catch(() => { alert('Der opstod en fejl.'); loading = false; });
                    "
                    :disabled="loading"
                    class="px-8 py-3 bg-amber-500 text-gray-900 rounded-lg font-bold hover:bg-amber-400 transition disabled:opacity-50">
                        <span x-show="!loading">Køb nu</span>
                        <span x-show="loading" x-cloak>Behandler...</span>
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($ebook['cover_image_path']): ?>
                <div class="flex justify-center">
                    <img src="<?= h($ebook['cover_image_path']) ?>" alt="<?= h($ebook['title']) ?>"
                        class="w-64 rounded-xl shadow-2xl transform hover:scale-105 transition duration-500">
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Description & Features -->
<section class="py-20 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if ($ebook['description']): ?>
            <div class="prose prose-lg max-w-none mb-16
                prose-headings:text-gray-900 prose-p:text-gray-700 prose-a:text-blue-400
                prose-strong:text-gray-900 prose-ul:text-gray-700">
                <?= $ebook['description'] ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($features)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($features as $feature): ?>
                    <div class="bg-white rounded-xl p-6 border border-gray-200">
                        <?php if (!empty($feature['icon'])): ?>
                            <span class="text-2xl mb-3 block"><?= $feature['icon'] ?></span>
                        <?php endif; ?>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= h($feature['title'] ?? '') ?></h3>
                        <p class="text-gray-600 text-sm"><?= h($feature['description'] ?? '') ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mt-12">
            <a href="/eboger" class="text-blue-600 hover:text-blue-700 font-medium transition">&larr; Alle e-bøger</a>
        </div>
    </div>
</section>
