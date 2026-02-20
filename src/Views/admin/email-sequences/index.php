<?php $pageTitle = 'Email Sequences'; $currentPage = 'email-sequences'; $tenant = currentTenant(); ob_start(); ?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-white">Email Sequences</h2>
        <p class="text-sm text-gray-400 mt-1">Automate email follow-ups based on triggers like signups, purchases, or course enrollments.</p>
    </div>
    <a href="/admin/email-sequences/create" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Create Sequence
    </a>
</div>

<div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
    <?php if (empty($sequences)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <p class="text-gray-400 mb-4">No email sequences yet. Create your first sequence to start automating email follow-ups.</p>
            <a href="/admin/email-sequences/create" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                Create Sequence
            </a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Trigger</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Steps</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Enrollments</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($sequences as $sequence): ?>
                    <tr class="hover:bg-gray-750" x-data="{ confirmDelete: false }">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-white"><?= h($sequence['name']) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                            $triggerLabels = [
                                'manual' => 'Manual',
                                'quiz_completion' => 'Quiz Completion',
                                'lead_magnet_signup' => 'Lead Magnet Signup',
                                'purchase' => 'Purchase',
                                'course_enrollment' => 'Course Enrollment',
                            ];
                            $triggerColors = [
                                'manual' => 'bg-gray-700 text-gray-300',
                                'quiz_completion' => 'bg-purple-900 text-purple-300',
                                'lead_magnet_signup' => 'bg-blue-900 text-blue-300',
                                'purchase' => 'bg-emerald-900 text-emerald-300',
                                'course_enrollment' => 'bg-cyan-900 text-cyan-300',
                            ];
                            $triggerLabel = $triggerLabels[$sequence['trigger_type']] ?? ucfirst($sequence['trigger_type']);
                            $triggerColor = $triggerColors[$sequence['trigger_type']] ?? 'bg-gray-700 text-gray-300';
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $triggerColor ?>">
                                <?= $triggerLabel ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                            $statusColors = [
                                'active' => 'bg-green-900 text-green-300',
                                'paused' => 'bg-yellow-900 text-yellow-300',
                                'draft' => 'bg-gray-700 text-gray-300',
                            ];
                            $statusClass = $statusColors[$sequence['status']] ?? 'bg-gray-700 text-gray-300';
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                                <?= ucfirst($sequence['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-300 text-center"><?= number_format($sequence['step_count']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-300 text-center"><?= number_format($sequence['enrollment_count']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-400"><?= formatDate($sequence['created_at']) ?></td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="/admin/email-sequences/edit?id=<?= $sequence['id'] ?>" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                                    Edit
                                </a>
                                <template x-if="!confirmDelete">
                                    <button @click="confirmDelete = true" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-400 bg-gray-700 hover:bg-red-900/50 rounded-lg transition">
                                        Delete
                                    </button>
                                </template>
                                <template x-if="confirmDelete">
                                    <form method="POST" action="/admin/email-sequences/delete" class="inline-flex items-center space-x-1">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $sequence['id'] ?>">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                                            Confirm
                                        </button>
                                        <button type="button" @click="confirmDelete = false" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                                            Cancel
                                        </button>
                                    </form>
                                </template>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
