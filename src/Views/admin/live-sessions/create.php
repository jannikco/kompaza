<?php
$pageTitle = 'Create Live Session';
$currentPage = 'live-sessions';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/live-sessions" class="text-sm text-gray-500 hover:text-gray-900 transition">&larr; Back to Live Sessions</a>
    <h2 class="text-2xl font-bold text-gray-900 mt-1">Create Live Session</h2>
    <p class="text-sm text-gray-500 mt-1">Schedule a new live session for your members.</p>
</div>

<form method="POST" action="/admin/live-sessions/store" class="max-w-4xl">
    <?= csrfField() ?>

    <!-- Session Details -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Session Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-600">*</span></label>
                <input type="text" id="title" name="title" required value="<?= h($_POST['title'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="e.g. Weekly Q&A Session">
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea id="description" name="description" rows="4"
                          class="w-full px-4 py-3 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                          placeholder="Describe what this session will cover..."><?= h($_POST['description'] ?? '') ?></textarea>
            </div>

            <div>
                <label for="scheduled_at" class="block text-sm font-medium text-gray-700 mb-1.5">Date & Time <span class="text-red-600">*</span></label>
                <input type="datetime-local" id="scheduled_at" name="scheduled_at" required value="<?= h($_POST['scheduled_at'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            </div>

            <div>
                <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-1.5">Duration (minutes)</label>
                <input type="number" id="duration_minutes" name="duration_minutes" min="15" step="15" value="<?= h($_POST['duration_minutes'] ?? '60') ?>"
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                       placeholder="60">
            </div>

            <div>
                <label for="meeting_url" class="block text-sm font-medium text-gray-700 mb-1.5">Meeting URL</label>
                <input type="url" id="meeting_url" name="meeting_url" value="<?= h($_POST['meeting_url'] ?? '') ?>"
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
                    <option value="0" <?= ($_POST['min_tier_level'] ?? '0') === '0' ? 'selected' : '' ?>>Free</option>
                    <option value="1" <?= ($_POST['min_tier_level'] ?? '') === '1' ? 'selected' : '' ?>>Pro</option>
                    <option value="2" <?= ($_POST['min_tier_level'] ?? '') === '2' ? 'selected' : '' ?>>Premium</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Only members with this tier or higher can register.</p>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                <select id="status" name="status" class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="scheduled" <?= ($_POST['status'] ?? 'scheduled') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                    <option value="live" <?= ($_POST['status'] ?? '') === 'live' ? 'selected' : '' ?>>Live</option>
                    <option value="completed" <?= ($_POST['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= ($_POST['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end space-x-3">
        <a href="/admin/live-sessions" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition">Cancel</a>
        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Session
        </button>
    </div>
</form>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
