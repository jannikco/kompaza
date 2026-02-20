<?php $pageTitle = 'Edit Company: ' . h($company['company_name']); $currentPage = 'companies'; $tenant = currentTenant(); ob_start(); ?>

<div class="mb-6">
    <a href="/admin/companies" class="inline-flex items-center text-sm text-gray-400 hover:text-white transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Company Accounts
    </a>
</div>

<!-- Company Info Form -->
<form method="POST" action="/admin/companies/update" class="space-y-8 mb-10">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $company['id'] ?>">

    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Company Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="company_name" class="block text-sm font-medium text-gray-300 mb-2">Company Name *</label>
                <input type="text" name="company_name" id="company_name" required
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    value="<?= h($company['company_name']) ?>">
            </div>
            <div>
                <label for="admin_user_id" class="block text-sm font-medium text-gray-300 mb-2">Account Admin</label>
                <select name="admin_user_id" id="admin_user_id"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">-- Select a customer --</option>
                    <?php foreach ($customers as $customer): ?>
                    <option value="<?= $customer['id'] ?>" <?= $company['admin_user_id'] == $customer['id'] ? 'selected' : '' ?>>
                        <?= h($customer['name']) ?> (<?= h($customer['email']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="total_licenses" class="block text-sm font-medium text-gray-300 mb-2">Total Licenses</label>
                <input type="number" name="total_licenses" id="total_licenses" min="0"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    value="<?= (int)$company['total_licenses'] ?>">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                <select name="status" id="status"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="active" <?= $company['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="suspended" <?= $company['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    <option value="inactive" <?= $company['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                Update Company
            </button>
        </div>
    </div>
</form>

<!-- Team Members Section -->
<div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-8">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-white">Team Members</h3>
        <span class="text-sm text-gray-400"><?= count($members) ?> member<?= count($members) !== 1 ? 's' : '' ?></span>
    </div>

    <?php if (!empty($members)): ?>
    <div class="overflow-x-auto mb-6">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <?php foreach ($members as $member): ?>
                <tr x-data="{ confirmRemove: false }">
                    <td class="px-4 py-3 text-sm text-white"><?= h($member['user_name'] ?? $member['name'] ?? 'N/A') ?></td>
                    <td class="px-4 py-3 text-sm text-gray-300"><?= h($member['user_email'] ?? $member['email']) ?></td>
                    <td class="px-4 py-3">
                        <?php
                        $memberStatusColors = [
                            'active' => 'bg-green-900 text-green-300',
                            'invited' => 'bg-yellow-900 text-yellow-300',
                            'inactive' => 'bg-gray-700 text-gray-300',
                        ];
                        $memberStatusClass = $memberStatusColors[$member['status']] ?? 'bg-gray-700 text-gray-300';
                        ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $memberStatusClass ?>">
                            <?= ucfirst($member['status']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <template x-if="!confirmRemove">
                            <button @click="confirmRemove = true" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-400 bg-gray-700 hover:bg-red-900/50 rounded-lg transition">
                                Remove
                            </button>
                        </template>
                        <template x-if="confirmRemove">
                            <form method="POST" action="/admin/companies/member-remove" class="inline-flex items-center space-x-1">
                                <?= csrfField() ?>
                                <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
                                <input type="hidden" name="company_id" value="<?= $company['id'] ?>">
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                                    Confirm
                                </button>
                                <button type="button" @click="confirmRemove = false" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                                    Cancel
                                </button>
                            </form>
                        </template>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <p class="text-gray-500 text-sm mb-6">No team members yet.</p>
    <?php endif; ?>

    <!-- Add Member Form -->
    <div class="border-t border-gray-700 pt-6">
        <h4 class="text-sm font-semibold text-gray-300 mb-4">Add Team Member</h4>
        <form method="POST" action="/admin/companies/member-add" class="flex flex-col sm:flex-row items-end gap-4">
            <?= csrfField() ?>
            <input type="hidden" name="company_id" value="<?= $company['id'] ?>">
            <div class="flex-1 w-full">
                <label for="member_name" class="block text-sm font-medium text-gray-400 mb-1">Name</label>
                <input type="text" name="name" id="member_name"
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                    placeholder="John Doe">
            </div>
            <div class="flex-1 w-full">
                <label for="member_email" class="block text-sm font-medium text-gray-400 mb-1">Email *</label>
                <input type="email" name="email" id="member_email" required
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                    placeholder="john@example.com">
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition whitespace-nowrap">
                Add Member
            </button>
        </form>
    </div>
</div>

<!-- Licenses Section -->
<div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-white">Course Licenses</h3>
        <span class="text-sm text-gray-400"><?= count($licenses) ?> license<?= count($licenses) !== 1 ? 's' : '' ?></span>
    </div>

    <?php if (!empty($licenses)): ?>
    <div class="overflow-x-auto mb-6">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Course</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Seats (Used / Total)</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Expires</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <?php foreach ($licenses as $license): ?>
                <tr x-data="{ confirmRemove: false }">
                    <td class="px-4 py-3 text-sm text-white"><?= h($license['course_title'] ?? 'Unknown Course') ?></td>
                    <td class="px-4 py-3 text-sm text-gray-300 text-center">
                        <?= (int)$license['seats_used'] ?> / <?= (int)$license['seats_total'] ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-300">
                        <?= $license['expires_at'] ? formatDate($license['expires_at']) : 'Never' ?>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <template x-if="!confirmRemove">
                            <button @click="confirmRemove = true" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-400 bg-gray-700 hover:bg-red-900/50 rounded-lg transition">
                                Remove
                            </button>
                        </template>
                        <template x-if="confirmRemove">
                            <form method="POST" action="/admin/companies/license-remove" class="inline-flex items-center space-x-1">
                                <?= csrfField() ?>
                                <input type="hidden" name="license_id" value="<?= $license['id'] ?>">
                                <input type="hidden" name="company_id" value="<?= $company['id'] ?>">
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                                    Confirm
                                </button>
                                <button type="button" @click="confirmRemove = false" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                                    Cancel
                                </button>
                            </form>
                        </template>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <p class="text-gray-500 text-sm mb-6">No course licenses assigned yet.</p>
    <?php endif; ?>

    <!-- Add License Form -->
    <div class="border-t border-gray-700 pt-6">
        <h4 class="text-sm font-semibold text-gray-300 mb-4">Add Course License</h4>
        <form method="POST" action="/admin/companies/license-add" class="flex flex-col sm:flex-row items-end gap-4">
            <?= csrfField() ?>
            <input type="hidden" name="company_id" value="<?= $company['id'] ?>">
            <div class="flex-1 w-full">
                <label for="course_id" class="block text-sm font-medium text-gray-400 mb-1">Course *</label>
                <select name="course_id" id="course_id" required
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                    <option value="">-- Select a course --</option>
                    <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['id'] ?>"><?= h($course['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="w-full sm:w-32">
                <label for="seats_total" class="block text-sm font-medium text-gray-400 mb-1">Seats</label>
                <input type="number" name="seats_total" id="seats_total" min="1" value="1"
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
            </div>
            <div class="w-full sm:w-48">
                <label for="expires_at" class="block text-sm font-medium text-gray-400 mb-1">Expires</label>
                <input type="date" name="expires_at" id="expires_at"
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition whitespace-nowrap">
                Add License
            </button>
        </form>
    </div>
</div>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
