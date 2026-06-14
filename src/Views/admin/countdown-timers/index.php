<?php
$pageTitle = 'Countdown Timers';
$currentPage = 'countdown-timers';
$tenant = currentTenant();
ob_start();
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Countdown Timers</h2>
        <p class="text-sm text-gray-500 mt-1">Create urgency with countdown timers on your pages</p>
    </div>
    <a href="/admin/countdown-timers/create" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Timer
    </a>
</div>

<!-- Embed Code Info -->
<div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
    <p class="text-sm text-blue-800">
        <strong>How to use:</strong> Copy the embed code from any timer and paste it into your landing page, custom page, or email template HTML.
    </p>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <?php if (empty($timers)): ?>
        <div class="p-12 text-center text-gray-500">
            <p class="text-lg mb-2">No countdown timers yet.</p>
            <p class="text-sm">Create a timer to add urgency to your offers.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Headline</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">End / Duration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Embed</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($timers as $timer): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= h($timer['name']) ?></td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $timer['timer_type'] === 'fixed' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' ?>">
                                <?= ucfirst($timer['timer_type']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= h($timer['headline'] ?: '-') ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?php if ($timer['timer_type'] === 'fixed'): ?>
                                <?= $timer['end_date'] ? date('d/m/Y H:i', strtotime($timer['end_date'])) : '-' ?>
                            <?php else: ?>
                                <?= (int)$timer['duration_minutes'] ?> min
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $timer['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                                <?= ucfirst($timer['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4" x-data="{ copied: false }">
                            <button @click="navigator.clipboard.writeText('<div data-countdown-timer-id=\'<?= (int)$timer['id'] ?>\'></div>'); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="text-xs font-medium text-indigo-600 hover:text-indigo-800 transition">
                                <span x-show="!copied">Copy Code</span>
                                <span x-show="copied" x-cloak class="text-green-600">Copied!</span>
                            </button>
                        </td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <a href="/admin/countdown-timers/edit?id=<?= (int)$timer['id'] ?>" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</a>
                            <form method="POST" action="/admin/countdown-timers/delete" class="inline" onsubmit="return confirm('Delete this timer?')">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int)$timer['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
