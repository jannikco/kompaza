<?php
$isEdit = !empty($test);
$pageTitle = $isEdit ? 'Edit A/B Test' : 'Create A/B Test';
$currentPage = 'ab-tests';
ob_start();
?>

<form method="POST" action="<?= $isEdit ? '/admin/ab-tests/update' : '/admin/ab-tests/store' ?>"
      x-data="abTestForm()" class="max-w-4xl">
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
    <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= $test['id'] ?>">
    <?php endif; ?>

    <!-- Test Info -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Test Configuration</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Test Name</label>
                <input type="text" name="name" value="<?= h($test['name'] ?? '') ?>" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Page Type</label>
                <select name="original_type" x-model="originalType" <?= ($isEdit && $test['status'] === 'running') ? 'disabled' : '' ?>
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="lead_magnet">Lead Magnet</option>
                    <option value="product">Product Page</option>
                </select>
                <input type="hidden" name="test_type" :value="originalType === 'lead_magnet' ? 'landing_page' : 'product_page'">
            </div>
            <div x-show="originalType === 'lead_magnet'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Original Page (Control)</label>
                <select name="original_id" <?= ($isEdit && $test['status'] === 'running') ? 'disabled' : '' ?>
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select lead magnet...</option>
                    <?php foreach ($leadMagnets as $lm): ?>
                    <option value="<?= $lm['id'] ?>" <?= ($test['original_id'] ?? '') == $lm['id'] && ($test['original_type'] ?? '') === 'lead_magnet' ? 'selected' : '' ?>><?= h($lm['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div x-show="originalType === 'product'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Original Page (Control)</label>
                <select name="original_id" <?= ($isEdit && $test['status'] === 'running') ? 'disabled' : '' ?>
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select product...</option>
                    <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($test['original_id'] ?? '') == $p['id'] && ($test['original_type'] ?? '') === 'product' ? 'selected' : '' ?>><?= h($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Control Traffic Weight (%)</label>
                <input type="number" name="control_weight" value="<?= h($variants[0]['traffic_weight'] ?? 50) ?>" min="1" max="99"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>
    </div>

    <!-- Variants -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Challenger Variants</h3>
            <?php if (!$isEdit || $test['status'] !== 'running'): ?>
            <button type="button" @click="addVariant()" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">+ Add Variant</button>
            <?php endif; ?>
        </div>

        <template x-for="(variant, index) in variants" :key="index">
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 mb-3">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-700" x-text="'Variant ' + String.fromCharCode(65 + index)"></span>
                    <?php if (!$isEdit || $test['status'] !== 'running'): ?>
                    <button type="button" @click="removeVariant(index)" class="text-red-500 hover:text-red-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <?php endif; ?>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Variant Name</label>
                        <input type="text" :name="'variant_name[' + index + ']'" x-model="variant.name"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg" placeholder="e.g. New Headline">
                    </div>
                    <div x-show="originalType === 'lead_magnet'">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Lead Magnet Variant</label>
                        <select :name="'variant_page_id[' + index + ']'" x-model="variant.page_id"
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                            <option value="">Select...</option>
                            <?php foreach ($leadMagnets as $lm): ?>
                            <option value="<?= $lm['id'] ?>"><?= h($lm['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div x-show="originalType === 'product'">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Product Variant</label>
                        <select :name="'variant_page_id[' + index + ']'" x-model="variant.page_id"
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                            <option value="">Select...</option>
                            <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= h($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Traffic Weight (%)</label>
                        <input type="number" :name="'variant_weight[' + index + ']'" x-model="variant.weight" min="1" max="99"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    </div>
                </div>
            </div>
        </template>

        <div x-show="variants.length === 0" class="text-center py-6 text-gray-500 text-sm">
            Add at least one challenger variant to test against your original page.
        </div>
    </div>

    <?php if ($isEdit): ?>
    <!-- Results -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Results</h3>
        <?php if (empty($variants)): ?>
            <p class="text-sm text-gray-500">No variants configured yet.</p>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($variants as $v): ?>
            <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-900"><?= h($v['name']) ?></span>
                        <?php if ($v['is_control']): ?>
                        <span class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">Control</span>
                        <?php endif; ?>
                        <?php if ($test['winner_variant_id'] && (int)$test['winner_variant_id'] === (int)$v['id']): ?>
                        <span class="text-xs bg-green-100 text-green-700 px-1.5 py-0.5 rounded">Winner</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-gray-500"><?= (int)$v['traffic_weight'] ?>% traffic</p>
                </div>
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-900"><?= number_format($v['views']) ?></p>
                    <p class="text-xs text-gray-500">Views</p>
                </div>
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-900"><?= number_format($v['conversions']) ?></p>
                    <p class="text-xs text-gray-500">Conversions</p>
                </div>
                <div class="text-center">
                    <?php $rate = $v['views'] > 0 ? round($v['conversions'] / $v['views'] * 100, 1) : 0; ?>
                    <p class="text-sm font-semibold <?= $rate > 0 ? 'text-green-600' : 'text-gray-900' ?>"><?= $rate ?>%</p>
                    <p class="text-xs text-gray-500">Conv. Rate</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Test Actions</h3>
        <div class="flex flex-wrap gap-3">
            <?php if ($test['status'] === 'draft' || $test['status'] === 'paused'): ?>
            <form method="POST" action="/admin/ab-tests/start">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="id" value="<?= $test['id'] ?>">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">Start Test</button>
            </form>
            <?php endif; ?>
            <?php if ($test['status'] === 'running'): ?>
            <form method="POST" action="/admin/ab-tests/stop">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="id" value="<?= $test['id'] ?>">
                <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition">Stop Test</button>
            </form>
            <!-- Pick Winner -->
            <?php foreach ($variants as $v): ?>
            <form method="POST" action="/admin/ab-tests/stop" class="inline">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="id" value="<?= $test['id'] ?>">
                <input type="hidden" name="winner_variant_id" value="<?= $v['id'] ?>">
                <button type="submit" class="px-3 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                    Pick "<?= h($v['name']) ?>" as Winner
                </button>
            </form>
            <?php endforeach; ?>
            <?php endif; ?>
            <?php if ($test['status'] !== 'running'): ?>
            <form method="POST" action="/admin/ab-tests/delete" onsubmit="return confirm('Delete this test?')">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="id" value="<?= $test['id'] ?>">
                <button type="submit" class="px-4 py-2 bg-white text-red-600 text-sm font-medium rounded-lg border border-red-300 hover:bg-red-50 transition">Delete Test</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
            <?= $isEdit ? 'Update Test' : 'Create Test' ?>
        </button>
        <a href="/admin/ab-tests" class="px-6 py-2.5 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition">Cancel</a>
    </div>
</form>

<script>
function abTestForm() {
    return {
        originalType: '<?= h($test['original_type'] ?? 'lead_magnet') ?>',
        variants: <?= json_encode(array_values(array_map(function($v) {
            return ['name' => $v['name'], 'page_id' => (string)$v['variant_id'], 'weight' => (int)$v['traffic_weight']];
        }, array_filter($variants, fn($v) => !$v['is_control'])))) ?>,
        addVariant() {
            this.variants.push({ name: '', page_id: '', weight: 50 });
        },
        removeVariant(index) {
            this.variants.splice(index, 1);
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
