<?php
$pageTitle = 'Edit Session: ' . h($session['title']);
$currentPage = 'live-sessions';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/live-sessions" class="text-sm text-gray-500 hover:text-gray-900 transition">&larr; Back to Live Sessions</a>
    <h2 class="text-2xl font-bold text-gray-900 mt-1">Edit Live Session</h2>
    <p class="text-sm text-gray-500 mt-1">Update details for <?= h($session['title']) ?>.</p>
</div>

<form method="POST" action="/admin/live-sessions/update" class="max-w-4xl">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $session['id'] ?>">

    <!-- Session Details -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Session Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-600">*</span></label>
                <input type="text" id="title" name="title" required value="<?= h($session['title']) ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="e.g. Weekly Q&A Session">
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea id="description" name="description" rows="4"
                          class="w-full px-4 py-3 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                          placeholder="Describe what this session will cover..."><?= h($session['description'] ?? '') ?></textarea>
            </div>

            <div>
                <label for="scheduled_at" class="block text-sm font-medium text-gray-700 mb-1.5">Date & Time <span class="text-red-600">*</span></label>
                <input type="datetime-local" id="scheduled_at" name="scheduled_at" required
                       value="<?= !empty($session['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($session['scheduled_at'])) : '' ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            </div>

            <div>
                <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-1.5">Duration (minutes)</label>
                <input type="number" id="duration_minutes" name="duration_minutes" min="15" step="15" value="<?= h($session['duration_minutes'] ?? 60) ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="60">
            </div>

            <div>
                <label for="meeting_url" class="block text-sm font-medium text-gray-700 mb-1.5">Meeting URL</label>
                <input type="url" id="meeting_url" name="meeting_url" value="<?= h($session['meeting_url'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="https://zoom.us/j/...">
                <p class="text-xs text-gray-500 mt-1">Zoom, Google Meet, or any video conferencing link.</p>
            </div>
        </div>
    </div>

    <!-- Settings -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Settings</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="min_tier_level" class="block text-sm font-medium text-gray-700 mb-1.5">Minimum Membership Tier</label>
                <select id="min_tier_level" name="min_tier_level" class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="0" <?= (int)($session['min_tier_level'] ?? 0) === 0 ? 'selected' : '' ?>>Free</option>
                    <option value="1" <?= (int)($session['min_tier_level'] ?? 0) === 1 ? 'selected' : '' ?>>Pro</option>
                    <option value="2" <?= (int)($session['min_tier_level'] ?? 0) === 2 ? 'selected' : '' ?>>Premium</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Only members with this tier or higher can register.</p>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                <select id="status" name="status" class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="scheduled" <?= ($session['status'] ?? 'scheduled') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                    <option value="live" <?= ($session['status'] ?? '') === 'live' ? 'selected' : '' ?>>Live</option>
                    <option value="completed" <?= ($session['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= ($session['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
        </div>
    </div>

    <?php if (!empty($registrations)): ?>
    <!-- Registrations -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Registrations (<?= count($registrations) ?>)</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Registered</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($registrations as $r): ?>
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900"><?= h($r['user_name'] ?? '-') ?></td>
                        <td class="px-4 py-2 text-sm text-gray-500"><?= h($r['user_email'] ?? '-') ?></td>
                        <td class="px-4 py-2 text-sm text-gray-500"><?= formatDate($r['registered_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <div class="flex items-center justify-between">
        <div x-data="{ confirmDelete: false }">
            <template x-if="!confirmDelete">
                <button type="button" @click="confirmDelete = true" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete Session
                </button>
            </template>
            <template x-if="confirmDelete">
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-red-600">Are you sure?</span>
                    <button type="button" onclick="document.getElementById('delete-form').submit();" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                        Yes, Delete
                    </button>
                    <button type="button" @click="confirmDelete = false" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                        Cancel
                    </button>
                </div>
            </template>
        </div>

        <div class="flex items-center space-x-3">
            <a href="/admin/live-sessions" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition">Cancel</a>
            <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Update Session
            </button>
        </div>
    </div>
</form>

<!-- Hidden delete form -->
<form id="delete-form" method="POST" action="/admin/live-sessions/delete" class="hidden">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $session['id'] ?>">
</form>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
