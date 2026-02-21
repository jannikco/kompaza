<?php
$pageTitle = 'Edit Redirect';
$currentPage = 'redirects';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <a href="/admin/redirects" class="inline-flex items-center text-sm text-gray-400 hover:text-white transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Redirects
    </a>
</div>

<form method="POST" action="/admin/redirects/update" class="space-y-8">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $redirect['id'] ?>">

    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Redirect Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="from_path" class="block text-sm font-medium text-gray-300 mb-2">From Path</label>
                <input type="text" name="from_path" id="from_path" required
                    value="<?= h($redirect['from_path']) ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono"
                    placeholder="/dk/ebook-atlas">
                <p class="text-xs text-gray-500 mt-1">The old URL path that should redirect. Must start with /</p>
            </div>
            <div>
                <label for="to_path" class="block text-sm font-medium text-gray-300 mb-2">To Path</label>
                <input type="text" name="to_path" id="to_path" required
                    value="<?= h($redirect['to_path']) ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono"
                    placeholder="/ebog/chatgpt-atlas-guide">
                <p class="text-xs text-gray-500 mt-1">The new destination. Relative path or full URL.</p>
            </div>
            <div>
                <label for="status_code" class="block text-sm font-medium text-gray-300 mb-2">Redirect Type</label>
                <select name="status_code" id="status_code"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="301" <?= (int)$redirect['status_code'] === 301 ? 'selected' : '' ?>>301 — Permanent (SEO)</option>
                    <option value="302" <?= (int)$redirect['status_code'] === 302 ? 'selected' : '' ?>>302 — Temporary</option>
                </select>
            </div>
            <div class="flex items-end">
                <label class="flex items-center space-x-3">
                    <input type="checkbox" name="is_active" value="1" <?= $redirect['is_active'] ? 'checked' : '' ?>
                        class="w-4 h-4 bg-gray-700 border-gray-600 rounded text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-medium text-gray-300">Active</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Statistics</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-400">Total Hits</p>
                <p class="text-2xl font-bold text-white"><?= number_format($redirect['hit_count'] ?? 0) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Last Hit</p>
                <p class="text-lg text-white"><?= $redirect['last_hit_at'] ? formatDate($redirect['last_hit_at']) : 'Never' ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Created</p>
                <p class="text-lg text-white"><?= formatDate($redirect['created_at']) ?></p>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end space-x-4">
        <a href="/admin/redirects" class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
            Update Redirect
        </button>
    </div>
</form>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
