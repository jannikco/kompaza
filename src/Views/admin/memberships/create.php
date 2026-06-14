<?php
$pageTitle = 'Create Membership Plan';
$currentPage = 'memberships';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/memberships" class="text-sm text-gray-500 hover:text-gray-900 transition">&larr; Back to Membership Plans</a>
    <h2 class="text-2xl font-bold text-gray-900 mt-1">Create Membership Plan</h2>
    <p class="text-sm text-gray-500 mt-1">Set up a new membership tier for your customers.</p>
</div>

<form method="POST" action="/admin/memberships/store" class="max-w-4xl" x-data="{ name: '' }">
    <?= csrfField() ?>

    <!-- Basic Information -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-600">*</span></label>
                <input type="text" id="name" name="name" required value="<?= h($_POST['name'] ?? '') ?>"
                       x-model="name"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="e.g. Pro Membership">
            </div>

            <div class="md:col-span-2">
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1.5">Slug <span class="text-red-600">*</span></label>
                <input type="text" id="slug" name="slug" required value="<?= h($_POST['slug'] ?? '') ?>"
                       :value="name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="pro-membership">
                <p class="text-xs text-gray-500 mt-1">URL-friendly identifier. Auto-generated from name.</p>
            </div>

            <div>
                <label for="tier_level" class="block text-sm font-medium text-gray-700 mb-1.5">Tier Level <span class="text-red-600">*</span></label>
                <input type="number" id="tier_level" name="tier_level" required min="0" value="<?= h($_POST['tier_level'] ?? '0') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="0">
                <p class="text-xs text-gray-500 mt-1">0 = Free, 1 = Pro, 2 = Premium, etc. Higher level = more access.</p>
            </div>

            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1.5">Sort Order</label>
                <input type="number" id="sort_order" name="sort_order" min="0" value="<?= h($_POST['sort_order'] ?? '0') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="0">
                <p class="text-xs text-gray-500 mt-1">Display order on the pricing page.</p>
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-4 py-3 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                          placeholder="Describe what this membership plan includes..."><?= h($_POST['description'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Pricing -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pricing</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="price_monthly" class="block text-sm font-medium text-gray-700 mb-1.5">Monthly Price (DKK) <span class="text-red-600">*</span></label>
                <div class="relative">
                    <input type="number" id="price_monthly" name="price_monthly" required step="0.01" min="0" value="<?= h($_POST['price_monthly'] ?? '') ?>"
                           class="w-full pl-4 pr-16 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                           placeholder="0.00">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 text-sm">DKK</span>
                    </div>
                </div>
            </div>

            <div>
                <label for="price_yearly" class="block text-sm font-medium text-gray-700 mb-1.5">Yearly Price (DKK)</label>
                <div class="relative">
                    <input type="number" id="price_yearly" name="price_yearly" step="0.01" min="0" value="<?= h($_POST['price_yearly'] ?? '') ?>"
                           class="w-full pl-4 pr-16 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                           placeholder="0.00">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 text-sm">DKK</span>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Leave empty if yearly billing is not available.</p>
            </div>

            <div>
                <label for="stripe_monthly_price_id" class="block text-sm font-medium text-gray-700 mb-1.5">Stripe Monthly Price ID</label>
                <input type="text" id="stripe_monthly_price_id" name="stripe_monthly_price_id" value="<?= h($_POST['stripe_monthly_price_id'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="price_...">
                <p class="text-xs text-gray-500 mt-1">From your Stripe dashboard.</p>
            </div>

            <div>
                <label for="stripe_yearly_price_id" class="block text-sm font-medium text-gray-700 mb-1.5">Stripe Yearly Price ID</label>
                <input type="text" id="stripe_yearly_price_id" name="stripe_yearly_price_id" value="<?= h($_POST['stripe_yearly_price_id'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="price_...">
                <p class="text-xs text-gray-500 mt-1">From your Stripe dashboard.</p>
            </div>

            <div>
                <label for="discount_percent" class="block text-sm font-medium text-gray-700 mb-1.5">Discount Percent</label>
                <div class="relative">
                    <input type="number" id="discount_percent" name="discount_percent" step="1" min="0" max="100" value="<?= h($_POST['discount_percent'] ?? '') ?>"
                           class="w-full pl-4 pr-12 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                           placeholder="0">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 text-sm">%</span>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Discount on products for members of this plan.</p>
            </div>
        </div>
    </div>

    <!-- Content Access -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Content Access</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="max_courses" class="block text-sm font-medium text-gray-700 mb-1.5">Max Courses</label>
                <input type="number" id="max_courses" name="max_courses" min="0" value="<?= h($_POST['max_courses'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="Leave empty for unlimited">
                <p class="text-xs text-gray-500 mt-1">Maximum number of courses accessible. Leave empty for unlimited.</p>
            </div>

            <div>
                <label for="max_ebooks" class="block text-sm font-medium text-gray-700 mb-1.5">Max Ebooks</label>
                <input type="number" id="max_ebooks" name="max_ebooks" min="0" value="<?= h($_POST['max_ebooks'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="Leave empty for unlimited">
                <p class="text-xs text-gray-500 mt-1">Maximum number of ebooks accessible. Leave empty for unlimited.</p>
            </div>
        </div>
    </div>

    <!-- Permissions -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Permissions</h3>
        <div class="space-y-4">
            <div class="flex items-center">
                <input type="checkbox" id="can_access_prompts" name="can_access_prompts" value="1"
                       <?= !empty($_POST['can_access_prompts']) ? 'checked' : '' ?>
                       class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500">
                <label for="can_access_prompts" class="ml-2 text-sm font-medium text-gray-700">Can access prompts</label>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="can_post_community" name="can_post_community" value="1"
                       <?= !empty($_POST['can_post_community']) ? 'checked' : '' ?>
                       class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500">
                <label for="can_post_community" class="ml-2 text-sm font-medium text-gray-700">Can post in community</label>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="can_access_live_qa" name="can_access_live_qa" value="1"
                       <?= !empty($_POST['can_access_live_qa']) ? 'checked' : '' ?>
                       class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500">
                <label for="can_access_live_qa" class="ml-2 text-sm font-medium text-gray-700">Can access live Q&A sessions</label>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="community_read_only" name="community_read_only" value="1"
                       <?= !empty($_POST['community_read_only']) ? 'checked' : '' ?>
                       class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500">
                <label for="community_read_only" class="ml-2 text-sm font-medium text-gray-700">Community read-only (can view but not post)</label>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="is_default" name="is_default" value="1"
                       <?= !empty($_POST['is_default']) ? 'checked' : '' ?>
                       class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500">
                <label for="is_default" class="ml-2 text-sm font-medium text-gray-700">Set as default plan (assigned to new signups)</label>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end space-x-3">
        <a href="/admin/memberships" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition">Cancel</a>
        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Plan
        </button>
    </div>
</form>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
