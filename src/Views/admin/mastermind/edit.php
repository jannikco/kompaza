<?php $pageTitle = 'Edit Program: ' . h($program['title']); $currentPage = 'mastermind'; $tenant = currentTenant(); ob_start(); ?>

<div class="mb-6">
    <a href="/admin/mastermind" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-900 transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Mastermind Programs
    </a>
</div>

<!-- Program Info Form -->
<form method="POST" action="/admin/mastermind/update" enctype="multipart/form-data" class="space-y-8 mb-10">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $program['id'] ?>">

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Program Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                <input type="text" name="title" id="title" required
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    value="<?= h($program['title']) ?>">
            </div>
            <div class="md:col-span-2">
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">URL Slug</label>
                <input type="text" name="slug" id="slug"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    value="<?= h($program['slug']) ?>">
            </div>
            <div class="md:col-span-2">
                <label for="short_description" class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                <textarea name="short_description" id="short_description" rows="2"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= h($program['short_description'] ?? '') ?></textarea>
            </div>
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Full Description</label>
                <input type="hidden" name="description" id="description-hidden" value="<?= h($program['description'] ?? '') ?>">
                <div id="description-editor" class="bg-white"><?= $program['description'] ?? '' ?></div>
            </div>
            <div>
                <label for="cover_image" class="block text-sm font-medium text-gray-700 mb-2">Cover Image</label>
                <?php if (!empty($program['cover_image_path'])): ?>
                <div class="mb-3">
                    <img src="<?= h(imageUrl($program['cover_image_path'])) ?>" alt="Cover" class="h-32 w-auto rounded-lg object-cover">
                </div>
                <?php endif; ?>
                <input type="file" name="cover_image" id="cover_image" accept="image/*"
                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 file:cursor-pointer">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" id="status"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="draft" <?= $program['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $program['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="archived" <?= $program['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                Update Program
            </button>
        </div>
    </div>
</form>

<!-- Tiers Section -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-8">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">Tiers</h3>
        <span class="text-sm text-gray-500"><?= count($tiers) ?> tier<?= count($tiers) !== 1 ? 's' : '' ?></span>
    </div>

    <?php if (!empty($tiers)): ?>
    <div class="space-y-4 mb-6">
        <?php foreach ($tiers as $tier): ?>
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-5" x-data="{ editing: false, confirmDelete: false }">
            <!-- View mode -->
            <div x-show="!editing">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="text-white font-medium"><?= h($tier['name']) ?></h4>
                        <?php if (!empty($tier['description'])): ?>
                        <p class="text-sm text-gray-500 mt-1"><?= h($tier['description']) ?></p>
                        <?php endif; ?>
                        <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                            <?php if ($tier['upfront_price_dkk'] > 0): ?>
                            <span>Upfront: <?= formatMoney($tier['upfront_price_dkk']) ?></span>
                            <?php endif; ?>
                            <?php if ($tier['monthly_price_dkk'] > 0): ?>
                            <span>Monthly: <?= formatMoney($tier['monthly_price_dkk']) ?></span>
                            <?php endif; ?>
                            <?php if ($tier['max_members']): ?>
                            <span>Max: <?= (int)$tier['max_members'] ?> members</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($tier['features'])): ?>
                        <div class="mt-2">
                            <?php foreach (explode("\n", $tier['features']) as $feature): ?>
                                <?php if (trim($feature)): ?>
                                <span class="inline-block text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded mr-1 mb-1"><?= h(trim($feature)) ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button @click="editing = true" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                            Edit
                        </button>
                        <template x-if="!confirmDelete">
                            <button @click="confirmDelete = true" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-gray-100 hover:bg-red-50 rounded-lg transition">
                                Delete
                            </button>
                        </template>
                        <template x-if="confirmDelete">
                            <form method="POST" action="/admin/mastermind/tier-delete" class="inline-flex items-center space-x-1">
                                <?= csrfField() ?>
                                <input type="hidden" name="tier_id" value="<?= $tier['id'] ?>">
                                <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                                    Confirm
                                </button>
                                <button type="button" @click="confirmDelete = false" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                    Cancel
                                </button>
                            </form>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Edit mode -->
            <div x-show="editing" x-cloak>
                <form method="POST" action="/admin/mastermind/tier-update" class="space-y-4">
                    <?= csrfField() ?>
                    <input type="hidden" name="tier_id" value="<?= $tier['id'] ?>">
                    <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Name *</label>
                            <input type="text" name="name" required value="<?= h($tier['name']) ?>"
                                class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Description</label>
                            <input type="text" name="description" value="<?= h($tier['description'] ?? '') ?>"
                                class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Upfront Price (DKK)</label>
                            <input type="number" name="upfront_price_dkk" step="0.01" min="0" value="<?= $tier['upfront_price_dkk'] ?>"
                                class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Monthly Price (DKK)</label>
                            <input type="number" name="monthly_price_dkk" step="0.01" min="0" value="<?= $tier['monthly_price_dkk'] ?>"
                                class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Max Members</label>
                            <input type="number" name="max_members" min="0" value="<?= $tier['max_members'] ?? '' ?>"
                                class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                                placeholder="Unlimited">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Features (one per line)</label>
                            <textarea name="features" rows="3"
                                class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"><?= h($tier['features'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                            Save Changes
                        </button>
                        <button type="button" @click="editing = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="text-gray-500 text-sm mb-6">No tiers defined yet. Add your first tier below.</p>
    <?php endif; ?>

    <!-- Add Tier Form -->
    <div class="border-t border-gray-200 pt-6">
        <h4 class="text-sm font-semibold text-gray-700 mb-4">Add New Tier</h4>
        <form method="POST" action="/admin/mastermind/tier-store" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Name *</label>
                    <input type="text" name="name" required
                        class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                        placeholder="e.g., Silver, Gold, Platinum">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Description</label>
                    <input type="text" name="description"
                        class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                        placeholder="Brief tier description">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Upfront Price (DKK)</label>
                    <input type="number" name="upfront_price_dkk" step="0.01" min="0" value="0"
                        class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Monthly Price (DKK)</label>
                    <input type="number" name="monthly_price_dkk" step="0.01" min="0" value="0"
                        class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Max Members</label>
                    <input type="number" name="max_members" min="0"
                        class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                        placeholder="Leave blank for unlimited">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Features (one per line)</label>
                    <textarea name="features" rows="3"
                        class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                        placeholder="Weekly group calls&#10;1-on-1 coaching&#10;Private Slack channel"></textarea>
                </div>
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                Add Tier
            </button>
        </form>
    </div>
</div>

<!-- Enrollments Section -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">Enrollments</h3>
        <span class="text-sm text-gray-500"><?= count($enrollments) ?> enrollment<?= count($enrollments) !== 1 ? 's' : '' ?></span>
    </div>

    <?php if (!empty($enrollments)): ?>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tier</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrolled</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($enrollments as $enrollment): ?>
                <tr>
                    <td class="px-4 py-3">
                        <div class="text-sm text-white"><?= h($enrollment['user_name'] ?? 'N/A') ?></div>
                        <div class="text-xs text-gray-500"><?= h($enrollment['user_email'] ?? '') ?></div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?= h($enrollment['tier_name'] ?? 'N/A') ?></td>
                    <td class="px-4 py-3">
                        <?php
                        $enrollStatusColors = [
                            'active' => 'bg-green-100 text-green-700',
                            'paused' => 'bg-yellow-100 text-yellow-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                            'completed' => 'bg-blue-100 text-blue-700',
                        ];
                        $enrollStatusClass = $enrollStatusColors[$enrollment['status']] ?? 'bg-gray-100 text-gray-700';
                        ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $enrollStatusClass ?>">
                            <?= ucfirst($enrollment['status']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500"><?= formatDate($enrollment['enrolled_at'] ?? $enrollment['created_at'] ?? '') ?></td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end space-x-1">
                            <?php if ($enrollment['status'] === 'active'): ?>
                            <form method="POST" action="/admin/mastermind/enrollment-update" class="inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="enrollment_id" value="<?= $enrollment['id'] ?>">
                                <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                                <input type="hidden" name="status" value="paused">
                                <button type="submit" class="px-2 py-1 text-xs font-medium text-yellow-600 bg-gray-100 hover:bg-yellow-50 rounded transition" title="Pause">
                                    Pause
                                </button>
                            </form>
                            <form method="POST" action="/admin/mastermind/enrollment-update" class="inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="enrollment_id" value="<?= $enrollment['id'] ?>">
                                <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="px-2 py-1 text-xs font-medium text-blue-600 bg-gray-100 hover:bg-blue-50 rounded transition" title="Complete">
                                    Complete
                                </button>
                            </form>
                            <form method="POST" action="/admin/mastermind/enrollment-update" class="inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="enrollment_id" value="<?= $enrollment['id'] ?>">
                                <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="px-2 py-1 text-xs font-medium text-red-600 bg-gray-100 hover:bg-red-50 rounded transition" title="Cancel">
                                    Cancel
                                </button>
                            </form>
                            <?php elseif ($enrollment['status'] === 'paused'): ?>
                            <form method="POST" action="/admin/mastermind/enrollment-update" class="inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="enrollment_id" value="<?= $enrollment['id'] ?>">
                                <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="px-2 py-1 text-xs font-medium text-green-600 bg-gray-100 hover:bg-green-50 rounded transition" title="Resume">
                                    Resume
                                </button>
                            </form>
                            <form method="POST" action="/admin/mastermind/enrollment-update" class="inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="enrollment_id" value="<?= $enrollment['id'] ?>">
                                <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="px-2 py-1 text-xs font-medium text-red-600 bg-gray-100 hover:bg-red-50 rounded transition" title="Cancel">
                                    Cancel
                                </button>
                            </form>
                            <?php elseif ($enrollment['status'] === 'cancelled'): ?>
                            <form method="POST" action="/admin/mastermind/enrollment-update" class="inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="enrollment_id" value="<?= $enrollment['id'] ?>">
                                <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="px-2 py-1 text-xs font-medium text-green-600 bg-gray-100 hover:bg-green-50 rounded transition" title="Reactivate">
                                    Reactivate
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <p class="text-gray-500 text-sm">No enrollments yet.</p>
    <?php endif; ?>
</div>

<script>initRichEditor('description-editor', 'description-hidden', { simple: true, height: 400 });</script>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
