<?php
$isEdit = !empty($sequence);
$pageTitle = $isEdit ? 'Edit Sequence' : 'Create Sequence';
$currentPage = 'email-sequences';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/email-sequences" class="inline-flex items-center text-sm text-gray-400 hover:text-white transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Sequences
    </a>
</div>

<!-- Sequence Settings Form -->
<form method="POST" action="<?= $isEdit ? '/admin/email-sequences/update' : '/admin/email-sequences/store' ?>" class="space-y-8">
    <?= csrfField() ?>
    <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= $sequence['id'] ?>">
    <?php endif; ?>

    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Sequence Settings</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Sequence Name *</label>
                <input type="text" name="name" id="name" required
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="e.g., Welcome Series, Post-Purchase Follow-up"
                    value="<?= h($sequence['name'] ?? '') ?>">
            </div>
            <div x-data="{ triggerType: '<?= h($sequence['trigger_type'] ?? 'manual') ?>' }">
                <label for="trigger_type" class="block text-sm font-medium text-gray-300 mb-2">Trigger Type</label>
                <select name="trigger_type" id="trigger_type" x-model="triggerType"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="manual" <?= ($sequence['trigger_type'] ?? 'manual') === 'manual' ? 'selected' : '' ?>>Manual</option>
                    <option value="quiz_completion" <?= ($sequence['trigger_type'] ?? '') === 'quiz_completion' ? 'selected' : '' ?>>Quiz Completion</option>
                    <option value="lead_magnet_signup" <?= ($sequence['trigger_type'] ?? '') === 'lead_magnet_signup' ? 'selected' : '' ?>>Lead Magnet Signup</option>
                    <option value="purchase" <?= ($sequence['trigger_type'] ?? '') === 'purchase' ? 'selected' : '' ?>>Purchase</option>
                    <option value="course_enrollment" <?= ($sequence['trigger_type'] ?? '') === 'course_enrollment' ? 'selected' : '' ?>>Course Enrollment</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Determines when contacts are automatically enrolled in this sequence.</p>
            </div>
            <div>
                <label for="trigger_id" class="block text-sm font-medium text-gray-300 mb-2">Trigger ID <span class="text-gray-500">(optional)</span></label>
                <input type="number" name="trigger_id" id="trigger_id"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="e.g., specific lead magnet or course ID"
                    value="<?= h($sequence['trigger_id'] ?? '') ?>">
                <p class="text-xs text-gray-500 mt-1">Link to a specific item (e.g., a lead magnet ID or course ID). Leave empty for all.</p>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                <select name="status" id="status"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="draft" <?= ($sequence['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="active" <?= ($sequence['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="paused" <?= ($sequence['status'] ?? '') === 'paused' ? 'selected' : '' ?>>Paused</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4 mt-6 pt-6 border-t border-gray-700">
            <a href="/admin/email-sequences" class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                <?= $isEdit ? 'Update Sequence' : 'Create Sequence' ?>
            </button>
        </div>
    </div>
</form>

<?php if ($isEdit): ?>
<!-- Steps Section -->
<div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mt-8" x-data="{ showAddStep: false, editingStep: null }">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-white">Sequence Steps</h3>
            <p class="text-sm text-gray-400 mt-1">Define the emails to send and when to send them.</p>
        </div>
        <button @click="showAddStep = !showAddStep; editingStep = null" type="button"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-indigo-400 bg-indigo-900/30 border border-indigo-700/50 hover:bg-indigo-900/50 rounded-lg transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Step
        </button>
    </div>

    <?php if (empty($steps)): ?>
        <div class="text-center py-8" x-show="!showAddStep">
            <svg class="w-10 h-10 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <p class="text-gray-400 text-sm">No steps yet. Add your first email step to build the sequence.</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($steps as $index => $step): ?>
            <div class="bg-gray-900 border border-gray-700 rounded-lg p-5" x-data="{ confirmDelete: false, editing: false }">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-600 text-white text-xs font-bold"><?= $index + 1 ?></span>
                        <div>
                            <p class="text-sm font-medium text-white"><?= h($step['subject']) ?></p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                Day <?= (int)$step['day_number'] ?>
                                &middot; Sort order: <?= (int)$step['sort_order'] ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button @click="editing = !editing; $nextTick(() => { if(editing) { tinymce.init({ selector: '#step-body-<?= $step['id'] ?>', height: 300, menubar: false, plugins: 'lists link', toolbar: 'undo redo | bold italic | bullist numlist | link', skin: 'oxide-dark', content_css: 'dark' }); } else { tinymce.get('step-body-<?= $step['id'] ?>') && tinymce.get('step-body-<?= $step['id'] ?>').remove(); } })"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                            <span x-text="editing ? 'Cancel' : 'Edit'"></span>
                        </button>
                        <template x-if="!confirmDelete">
                            <button @click="confirmDelete = true" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-400 bg-gray-700 hover:bg-red-900/50 rounded-lg transition">
                                Delete
                            </button>
                        </template>
                        <template x-if="confirmDelete">
                            <div class="inline-flex items-center space-x-1">
                                <form method="POST" action="/admin/email-sequences/step-delete" class="inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="step_id" value="<?= $step['id'] ?>">
                                    <input type="hidden" name="sequence_id" value="<?= $sequence['id'] ?>">
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                                        Confirm
                                    </button>
                                </form>
                                <button type="button" @click="confirmDelete = false" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                                    Cancel
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Inline Edit Form -->
                <div x-show="editing" x-cloak class="mt-4 pt-4 border-t border-gray-700">
                    <form method="POST" action="/admin/email-sequences/step-update" class="space-y-4">
                        <?= csrfField() ?>
                        <input type="hidden" name="step_id" value="<?= $step['id'] ?>">
                        <input type="hidden" name="sequence_id" value="<?= $sequence['id'] ?>">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Day Number</label>
                                <input type="number" name="day_number" min="0" value="<?= (int)$step['day_number'] ?>"
                                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Days after enrollment to send</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Subject *</label>
                                <input type="text" name="subject" required value="<?= h($step['subject']) ?>"
                                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Sort Order</label>
                                <input type="number" name="sort_order" min="0" value="<?= (int)$step['sort_order'] ?>"
                                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Email Body</label>
                            <textarea name="body_html" id="step-body-<?= $step['id'] ?>" rows="6"
                                class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= h($step['body_html'] ?? '') ?></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                                Update Step
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Add Step Form -->
    <div x-show="showAddStep" x-cloak class="mt-6 bg-gray-900 border border-indigo-700/50 rounded-lg p-5"
         x-init="$watch('showAddStep', val => { if(val) { $nextTick(() => { tinymce.init({ selector: '#new-step-body', height: 300, menubar: false, plugins: 'lists link', toolbar: 'undo redo | bold italic | bullist numlist | link', skin: 'oxide-dark', content_css: 'dark' }); }); } else { tinymce.get('new-step-body') && tinymce.get('new-step-body').remove(); } })">
        <h4 class="text-sm font-semibold text-white mb-4">Add New Step</h4>
        <form method="POST" action="/admin/email-sequences/step-store" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="sequence_id" value="<?= $sequence['id'] ?>">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Day Number</label>
                    <input type="number" name="day_number" min="0" value="0"
                        class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Days after enrollment to send this email</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Subject *</label>
                    <input type="text" name="subject" required placeholder="Email subject line"
                        class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Sort Order</label>
                    <input type="number" name="sort_order" min="0" value="<?= count($steps) ?>"
                        class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Email Body</label>
                <textarea name="body_html" id="new-step-body" rows="6"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Write your email content here..."></textarea>
            </div>
            <div class="flex items-center justify-end space-x-3">
                <button type="button" @click="showAddStep = false" class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                    Add Step
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Enrollments Section -->
<div class="bg-gray-800 border border-gray-700 rounded-xl p-6 mt-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-white">Enrollments</h3>
            <p class="text-sm text-gray-400 mt-1">Contacts currently in or who have completed this sequence.</p>
        </div>
        <span class="text-sm text-gray-400"><?= count($enrollments) ?> total</span>
    </div>

    <?php if (empty($enrollments)): ?>
        <div class="text-center py-8">
            <svg class="w-10 h-10 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-gray-400 text-sm">No enrollments yet. Contacts will appear here once they are enrolled in this sequence.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Current Step</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Enrolled</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Completed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($enrollments as $enrollment): ?>
                    <tr class="hover:bg-gray-750">
                        <td class="px-4 py-3 text-sm text-white"><?= h($enrollment['email']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-300"><?= h($enrollment['name'] ?? '-') ?></td>
                        <td class="px-4 py-3">
                            <?php
                            $enrollStatusColors = [
                                'active' => 'bg-green-900 text-green-300',
                                'completed' => 'bg-blue-900 text-blue-300',
                                'paused' => 'bg-yellow-900 text-yellow-300',
                                'cancelled' => 'bg-red-900 text-red-300',
                            ];
                            $enrollStatusClass = $enrollStatusColors[$enrollment['status']] ?? 'bg-gray-700 text-gray-300';
                            ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= $enrollStatusClass ?>">
                                <?= ucfirst($enrollment['status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-300 text-center">
                            <?= (int)$enrollment['current_step'] ?> / <?= count($steps) ?>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-400"><?= formatDate($enrollment['enrolled_at']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-400"><?= $enrollment['completed_at'] ? formatDate($enrollment['completed_at']) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
