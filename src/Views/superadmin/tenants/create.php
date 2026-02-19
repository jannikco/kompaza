<?php
$pageTitle = 'Create Tenant';
$currentPage = 'tenants';
ob_start();
?>

<div class="max-w-2xl">
    <div class="mb-6">
        <a href="/tenants" class="text-sm text-gray-400 hover:text-white inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Tenants
        </a>
    </div>

    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <form method="POST" action="/tenants/store" class="space-y-5">
            <?= csrfField() ?>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-1">Tenant Name</label>
                <input type="text" id="name" name="name" required
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Acme Corp">
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium text-gray-300 mb-1">Slug</label>
                <div class="flex items-center">
                    <input type="text" id="slug" name="slug" required
                        class="flex-1 px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-l-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="acme-corp">
                    <span class="px-4 py-2.5 bg-gray-600 border border-gray-600 rounded-r-lg text-gray-400 text-sm">.<?= PLATFORM_DOMAIN ?></span>
                </div>
                <p class="text-xs text-gray-500 mt-1">This will be the tenant's subdomain.</p>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Contact Email</label>
                <input type="email" id="email" name="email"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="contact@acme.com">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-300 mb-1">Status</label>
                    <select id="status" name="status"
                        class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="trial">Trial</option>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div>
                    <label for="plan_id" class="block text-sm font-medium text-gray-300 mb-1">Plan</label>
                    <select id="plan_id" name="plan_id"
                        class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">No plan</option>
                        <?php foreach ($plans as $plan): ?>
                        <option value="<?= $plan['id'] ?>"><?= h($plan['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label for="trial_ends_at" class="block text-sm font-medium text-gray-300 mb-1">Trial Ends</label>
                <input type="date" id="trial_ends_at" name="trial_ends_at"
                    value="<?= date('Y-m-d', strtotime('+14 days')) ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-700">
                <a href="/tenants" class="px-4 py-2.5 text-sm font-medium text-gray-300 hover:text-white transition">Cancel</a>
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-lg transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-800">
                    Create Tenant
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/superadmin/layouts/layout.php';
?>
