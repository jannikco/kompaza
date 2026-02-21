<?php
$pageTitle = 'Enrollments: ' . ($course['title'] ?? '');
$currentPage = 'courses';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/kurser/rediger?id=<?= $course['id'] ?>" class="text-sm text-gray-500 hover:text-gray-900 transition">&larr; Back to <?= h($course['title']) ?></a>
    <h2 class="text-2xl font-bold text-gray-900 mt-1">Students — <?= h($course['title']) ?></h2>
    <p class="text-sm text-gray-500 mt-1"><?= count($enrollments) ?> enrolled students</p>
</div>

<!-- Manual Enrollment -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 mb-6 max-w-xl" x-data="{ showForm: false }">
    <button @click="showForm = !showForm" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-500 font-medium transition" x-show="!showForm">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Manually Enroll Student
    </button>
    <form x-show="showForm" x-cloak method="POST" action="/admin/kurser/tilmeld" class="flex items-end gap-3">
        <?= csrfField() ?>
        <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
        <div class="flex-1">
            <label class="block text-xs font-medium text-gray-500 mb-1">Customer User ID</label>
            <input type="number" name="user_id" required min="1"
                   class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"
                   placeholder="User ID">
        </div>
        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Enroll</button>
        <button type="button" @click="showForm = false" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-900 transition">Cancel</button>
    </form>
</div>

<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <?php if (empty($enrollments)): ?>
        <div class="p-12 text-center">
            <p class="text-gray-500">No students enrolled yet.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrolled</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($enrollments as $enrollment): ?>
                    <tr class="hover:bg-gray-50 transition-colors" x-data="{ confirmUnenroll: false }">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900"><?= h($enrollment['user_name']) ?></div>
                            <div class="text-xs text-gray-500"><?= h($enrollment['user_email']) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                            $sourceColors = [
                                'free' => 'bg-green-100 text-green-700',
                                'purchase' => 'bg-blue-100 text-blue-700',
                                'subscription' => 'bg-purple-100 text-purple-700',
                                'manual' => 'bg-gray-100 text-gray-700',
                            ];
                            $srcColor = $sourceColors[$enrollment['enrollment_source']] ?? 'bg-gray-100 text-gray-700';
                            ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?= $srcColor ?>"><?= h(ucfirst($enrollment['enrollment_source'])) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <div class="w-24 bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-indigo-500 h-1.5 rounded-full" style="width: <?= (float)$enrollment['progress_percent'] ?>%"></div>
                                </div>
                                <span class="text-xs text-gray-500"><?= (int)$enrollment['completed_lessons'] ?>/<?= (int)$enrollment['total_lessons'] ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= formatDate($enrollment['enrolled_at']) ?></td>
                        <td class="px-6 py-4">
                            <?php if ($enrollment['status'] === 'active'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700"><?= h(ucfirst($enrollment['status'])) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <?php if ($enrollment['status'] === 'active'): ?>
                            <template x-if="!confirmUnenroll">
                                <button @click="confirmUnenroll = true" class="text-xs text-red-600 hover:text-red-300 transition">Unenroll</button>
                            </template>
                            <template x-if="confirmUnenroll">
                                <form method="POST" action="/admin/kurser/afmeld" class="inline-flex items-center space-x-1">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="enrollment_id" value="<?= $enrollment['id'] ?>">
                                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                    <button type="submit" class="text-xs text-white bg-red-600 hover:bg-red-700 px-2 py-1 rounded transition">Confirm</button>
                                    <button type="button" @click="confirmUnenroll = false" class="text-xs text-gray-500 px-2 py-1">Cancel</button>
                                </form>
                            </template>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
