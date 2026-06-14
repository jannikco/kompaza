<?php
$pageTitle = 'Webinars';
$currentPage = 'webinars';
ob_start();

$statusColors = ['draft' => 'bg-gray-100 text-gray-700', 'registration_open' => 'bg-green-100 text-green-700', 'live' => 'bg-red-100 text-red-700', 'replay' => 'bg-blue-100 text-blue-700', 'archived' => 'bg-gray-100 text-gray-500'];
$typeLabels = ['live' => 'Live', 'replay' => 'Replay', 'evergreen' => 'Evergreen'];
?>

<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">Host webinars with registration pages, reminders, and post-webinar offers.</p>
    <a href="/admin/webinars/create" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Webinar
    </a>
</div>

<?php if (empty($webinars)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
    <h3 class="text-lg font-medium text-gray-900 mb-2">No webinars yet</h3>
    <p class="text-gray-500 mb-4">Create webinar funnels to engage your audience with live or recorded presentations.</p>
    <a href="/admin/webinars/create" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">Create Webinar</a>
</div>
<?php else: ?>
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Webinar</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registrations</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Attended</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php foreach ($webinars as $w): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900"><?= h($w['title']) ?></div>
                    <div class="text-xs text-gray-500">/webinar/<?= h($w['slug']) ?></div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500"><?= $typeLabels[$w['webinar_type']] ?? ucfirst($w['webinar_type']) ?></td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium <?= $statusColors[$w['status']] ?? 'bg-gray-100 text-gray-700' ?>">
                        <?= ucfirst(str_replace('_', ' ', $w['status'])) ?>
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500"><?= $w['scheduled_at'] ? formatDate($w['scheduled_at']) : '-' ?></td>
                <td class="px-6 py-4 text-sm text-gray-900"><?= number_format($w['registration_count']) ?></td>
                <td class="px-6 py-4 text-sm text-gray-900"><?= number_format($w['attendance_count']) ?></td>
                <td class="px-6 py-4 text-right">
                    <a href="/webinar/<?= h($w['slug']) ?>" target="_blank" class="text-gray-500 hover:text-gray-700 text-sm mr-3">View</a>
                    <a href="/admin/webinars/edit?id=<?= $w['id'] ?>" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</a>
                    <form method="POST" action="/admin/webinars/delete" class="inline ml-3" onsubmit="return confirm('Delete this webinar?')">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="id" value="<?= $w['id'] ?>">
                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
