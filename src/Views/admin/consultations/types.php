<?php
$pageTitle = 'Consultation Types';
$currentPage = 'consultations';
$tenant = currentTenant();
ob_start();
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-white">Consultation Types</h2>
        <p class="text-sm text-gray-400 mt-1">Define the types of consultations you offer.</p>
    </div>
    <a href="/admin/consultations" class="inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 text-sm font-medium rounded-lg transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Bookings
    </a>
</div>

<!-- Add New Type Form -->
<div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-8">
    <h3 class="text-lg font-semibold text-white mb-4">Add New Consultation Type</h3>
    <form method="POST" action="/admin/consultations/type-store">
        <?= csrfField() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Name <span class="text-red-400">*</span></label>
                <input type="text" name="name" required
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm"
                       placeholder="e.g. Strategy Session">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Duration (minutes)</label>
                <input type="number" name="duration_minutes" value="60" min="15" step="15"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Price (DKK)</label>
                <input type="number" name="price_dkk" value="0" min="0" step="0.01"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Description</label>
                <input type="text" name="description"
                       class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm"
                       placeholder="Brief description of this consultation type">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Status</label>
                <select name="status" class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                Create Type
            </button>
        </div>
    </form>
</div>

<!-- Existing Types -->
<div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-700">
        <h3 class="text-lg font-semibold text-white">Existing Types</h3>
    </div>
    <?php if (empty($types)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <p class="text-gray-400">No consultation types yet. Create your first type above.</p>
        </div>
    <?php else: ?>
        <div class="divide-y divide-gray-700">
            <?php foreach ($types as $type): ?>
            <div class="p-6" x-data="{ editing: false }">
                <!-- View mode -->
                <div x-show="!editing" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <h4 class="text-base font-medium text-white"><?= h($type['name']) ?></h4>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= $type['status'] === 'active' ? 'bg-green-900 text-green-300' : 'bg-gray-700 text-gray-400' ?>">
                                <?= ucfirst($type['status']) ?>
                            </span>
                        </div>
                        <?php if ($type['description']): ?>
                            <p class="text-sm text-gray-400 mt-1"><?= h($type['description']) ?></p>
                        <?php endif; ?>
                        <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                            <span><?= $type['duration_minutes'] ?> minutes</span>
                            <span><?= formatMoney($type['price_dkk']) ?></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button @click="editing = true" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </button>
                        <form method="POST" action="/admin/consultations/type-delete" onsubmit="return confirm('Are you sure you want to delete this consultation type?')" class="inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="id" value="<?= $type['id'] ?>">
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-400 bg-gray-700 hover:bg-red-900/50 rounded-lg transition">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Edit mode -->
                <form x-show="editing" x-cloak method="POST" action="/admin/consultations/type-update">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= $type['id'] ?>">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Name <span class="text-red-400">*</span></label>
                            <input type="text" name="name" required value="<?= h($type['name']) ?>"
                                   class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Duration (minutes)</label>
                            <input type="number" name="duration_minutes" value="<?= (int)$type['duration_minutes'] ?>" min="15" step="15"
                                   class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Price (DKK)</label>
                            <input type="number" name="price_dkk" value="<?= number_format((float)$type['price_dkk'], 2, '.', '') ?>" min="0" step="0.01"
                                   class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Description</label>
                            <input type="text" name="description" value="<?= h($type['description'] ?? '') ?>"
                                   class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Status</label>
                            <select name="status" class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                                <option value="active" <?= $type['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $type['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-4">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                            Save Changes
                        </button>
                        <button type="button" @click="editing = false" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 text-sm font-medium rounded-lg transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
