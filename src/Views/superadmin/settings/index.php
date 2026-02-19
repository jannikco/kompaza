<?php
$pageTitle = 'Platform Settings';
$currentPage = 'settings';

// Build settings map
$settingsMap = [];
foreach ($settings as $s) {
    $settingsMap[$s['setting_key']] = $s['setting_value'];
}

ob_start();
?>

<div class="bg-gray-800 rounded-xl border border-gray-700 p-6 max-w-2xl">
    <h2 class="text-lg font-semibold text-white mb-6">Platform Settings</h2>

    <?php $flash = getFlashMessage(); ?>
    <?php if ($flash): ?>
    <div class="mb-4 p-3 rounded-lg <?= $flash['type'] === 'success' ? 'bg-green-900/50 border border-green-700 text-green-300' : 'bg-red-900/50 border border-red-700 text-red-300' ?>">
        <?= h($flash['message']) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/settings/update" class="space-y-4">
        <?= csrfField() ?>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Platform Name</label>
            <input type="text" name="platform_name" value="<?= h($settingsMap['platform_name'] ?? 'Kompaza') ?>" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Support Email</label>
            <input type="email" name="support_email" value="<?= h($settingsMap['support_email'] ?? '') ?>" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Default Trial Days</label>
            <input type="number" name="default_trial_days" value="<?= h($settingsMap['default_trial_days'] ?? '14') ?>" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="maintenance_mode" id="maintenance_mode" <?= !empty($settingsMap['maintenance_mode']) ? 'checked' : '' ?> class="rounded bg-gray-700 border-gray-600 text-indigo-600">
            <label for="maintenance_mode" class="text-sm text-gray-300">Maintenance Mode</label>
        </div>
        <div class="pt-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium">Save Settings</button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/superadmin/layouts/layout.php';
?>
