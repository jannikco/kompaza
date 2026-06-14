<?php
$isEdit = !empty($funnel);
$pageTitle = $isEdit ? 'Edit Funnel' : 'Create Funnel';
$currentPage = 'funnels';
ob_start();
?>

<form method="POST" action="<?= $isEdit ? '/admin/funnels/update' : '/admin/funnels/store' ?>"
      x-data="funnelForm()" class="max-w-4xl">
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
    <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= $funnel['id'] ?>">
    <?php endif; ?>

    <!-- Basic Info -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Funnel Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Funnel Name</label>
                <input type="text" name="name" value="<?= h($funnel['name'] ?? '') ?>" required
                       @input="if(!slugEdited) slug = $el.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" x-model="slug" @input="slugEdited = true" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Funnel Type</label>
                <select name="funnel_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="optin" <?= ($funnel['funnel_type'] ?? '') === 'optin' ? 'selected' : '' ?>>Opt-in Funnel</option>
                    <option value="sales" <?= ($funnel['funnel_type'] ?? 'sales') === 'sales' ? 'selected' : '' ?>>Sales Funnel</option>
                    <option value="webinar" <?= ($funnel['funnel_type'] ?? '') === 'webinar' ? 'selected' : '' ?>>Webinar Funnel</option>
                    <option value="launch" <?= ($funnel['funnel_type'] ?? '') === 'launch' ? 'selected' : '' ?>>Product Launch Funnel</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="draft" <?= ($funnel['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="active" <?= ($funnel['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="paused" <?= ($funnel['status'] ?? '') === 'paused' ? 'selected' : '' ?>>Paused</option>
                    <option value="archived" <?= ($funnel['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"><?= h($funnel['description'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Funnel Steps -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Funnel Steps</h3>
            <button type="button" @click="addStep()" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">+ Add Step</button>
        </div>

        <div class="space-y-4">
            <template x-for="(step, index) in steps" :key="index">
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 bg-indigo-600 text-white text-xs font-bold rounded-full flex items-center justify-center" x-text="index + 1"></span>
                            <span class="text-sm font-medium text-gray-700">Step</span>
                            <button type="button" @click="moveStep(index, -1)" x-show="index > 0" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            </button>
                            <button type="button" @click="moveStep(index, 1)" x-show="index < steps.length - 1" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </div>
                        <button type="button" @click="removeStep(index)" class="text-red-500 hover:text-red-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Step Name</label>
                            <input type="text" :name="'step_name[' + index + ']'" x-model="step.name"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg" placeholder="e.g. Landing Page">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Step Type</label>
                            <select :name="'step_type[' + index + ']'" x-model="step.step_type"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                                <option value="landing_page">Landing Page</option>
                                <option value="sales_page">Sales Page</option>
                                <option value="checkout">Checkout</option>
                                <option value="upsell">Upsell</option>
                                <option value="downsell">Downsell</option>
                                <option value="thank_you">Thank You</option>
                                <option value="webinar">Webinar</option>
                                <option value="email_sequence">Email Sequence</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Resource Type</label>
                            <select :name="'step_resource_type[' + index + ']'" x-model="step.resource_type"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                                <option value="">None (custom URL)</option>
                                <option value="lead_magnet">Lead Magnet</option>
                                <option value="product">Product</option>
                                <option value="email_sequence">Email Sequence</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                        <div x-show="step.resource_type === 'lead_magnet'">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Lead Magnet</label>
                            <select :name="'step_resource_id[' + index + ']'" x-model="step.resource_id"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                                <option value="">Select...</option>
                                <?php foreach ($leadMagnets as $lm): ?>
                                <option value="<?= $lm['id'] ?>"><?= h($lm['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div x-show="step.resource_type === 'product'">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Product</label>
                            <select :name="'step_resource_id[' + index + ']'" x-model="step.resource_id"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                                <option value="">Select...</option>
                                <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= h($p['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div x-show="step.resource_type === 'email_sequence'">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Email Sequence</label>
                            <select :name="'step_resource_id[' + index + ']'" x-model="step.resource_id"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                                <option value="">Select...</option>
                                <?php foreach ($emailSequences as $es): ?>
                                <option value="<?= $es['id'] ?>"><?= h($es['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div x-show="!step.resource_type">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Custom URL</label>
                            <input type="text" :name="'step_custom_url[' + index + ']'" x-model="step.custom_url"
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg" placeholder="https://...">
                            <input type="hidden" :name="'step_resource_id[' + index + ']'" value="">
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="steps.length === 0" class="text-center py-8 text-gray-500 text-sm">
            No steps yet. Click "Add Step" to build your funnel flow.
        </div>
    </div>

    <?php if ($isEdit): ?>
    <!-- Funnel Analytics -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Funnel Performance</h3>
        <div class="grid grid-cols-3 gap-4">
            <div class="text-center">
                <p class="text-2xl font-bold text-gray-900"><?= number_format($funnel['total_views']) ?></p>
                <p class="text-xs text-gray-500">Total Views</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-gray-900"><?= number_format($funnel['total_conversions']) ?></p>
                <p class="text-xs text-gray-500">Conversions</p>
            </div>
            <div class="text-center">
                <?php $rate = $funnel['total_views'] > 0 ? round($funnel['total_conversions'] / $funnel['total_views'] * 100, 1) : 0; ?>
                <p class="text-2xl font-bold text-gray-900"><?= $rate ?>%</p>
                <p class="text-xs text-gray-500">Conversion Rate</p>
            </div>
        </div>
        <?php if (!empty($steps)): ?>
        <div class="mt-6">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Step Performance</h4>
            <div class="space-y-2">
                <?php foreach ($steps as $i => $step): ?>
                <div class="flex items-center gap-3">
                    <span class="w-5 h-5 bg-indigo-100 text-indigo-600 text-xs font-bold rounded-full flex items-center justify-center"><?= $i + 1 ?></span>
                    <span class="text-sm text-gray-700 flex-1"><?= h($step['name']) ?></span>
                    <span class="text-xs text-gray-500"><?= number_format($step['views']) ?> views</span>
                    <span class="text-xs text-gray-500"><?= number_format($step['conversions']) ?> conv.</span>
                    <?php $stepRate = $step['views'] > 0 ? round($step['conversions'] / $step['views'] * 100, 1) : 0; ?>
                    <span class="text-xs font-medium <?= $stepRate > 5 ? 'text-green-600' : 'text-gray-500' ?>"><?= $stepRate ?>%</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
            <?= $isEdit ? 'Update Funnel' : 'Create Funnel' ?>
        </button>
        <a href="/admin/funnels" class="px-6 py-2.5 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition">Cancel</a>
    </div>
</form>

<script>
function funnelForm() {
    return {
        slug: '<?= h($funnel['slug'] ?? '') ?>',
        slugEdited: <?= $isEdit ? 'true' : 'false' ?>,
        steps: <?= json_encode(array_map(function($s) {
            return [
                'name' => $s['name'],
                'step_type' => $s['step_type'],
                'resource_type' => $s['resource_type'] ?? '',
                'resource_id' => $s['resource_id'] ?? '',
                'custom_url' => $s['custom_url'] ?? '',
            ];
        }, $steps)) ?>,
        addStep() {
            this.steps.push({ name: '', step_type: 'landing_page', resource_type: '', resource_id: '', custom_url: '' });
        },
        removeStep(index) {
            this.steps.splice(index, 1);
        },
        moveStep(index, direction) {
            const newIndex = index + direction;
            if (newIndex < 0 || newIndex >= this.steps.length) return;
            const temp = this.steps[index];
            this.steps[index] = this.steps[newIndex];
            this.steps[newIndex] = temp;
        }
    };
}
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
