<?php
$pageTitle = 'My Downloads';
$tenant = currentTenant();
ob_start();
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">My Downloads</h1>
        <p class="text-gray-600 mt-1">Access your purchased digital content.</p>
    </div>

    <div class="flex gap-4 mb-6 text-sm">
        <a href="/konto" class="text-gray-600 hover:text-gray-900">Dashboard</a>
        <a href="/konto/ordrer" class="text-gray-600 hover:text-gray-900">Orders</a>
        <a href="/konto/downloads" class="text-indigo-600 font-medium">Downloads</a>
        <a href="/konto/indstillinger" class="text-gray-600 hover:text-gray-900">Settings</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <?php if (!empty($downloads)): ?>
        <div class="divide-y divide-gray-200">
            <?php foreach ($downloads as $download): ?>
            <div class="p-6 flex items-center justify-between">
                <div>
                    <h3 class="font-medium text-gray-900"><?= h($download['name'] ?? $download['title'] ?? 'Download') ?></h3>
                    <p class="text-sm text-gray-500 mt-1">Purchased <?= formatDate($download['created_at']) ?></p>
                </div>
                <?php if (!empty($download['download_url'])): ?>
                <a href="<?= h($download['download_url']) ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Download
                </a>
                <?php else: ?>
                <span class="text-gray-400 text-sm">Not available</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-gray-500">No downloads yet.</p>
            <a href="/eboger" class="text-indigo-600 hover:text-indigo-700 text-sm mt-2 inline-block">Browse ebooks</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/shop/layout.php';
?>
