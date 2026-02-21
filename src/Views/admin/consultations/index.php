<?php
$pageTitle = 'Consultation Bookings';
$currentPage = 'consultations';
$tenant = currentTenant();

$statusBadgeColors = [
    'pending' => 'bg-yellow-100 text-yellow-700',
    'confirmed' => 'bg-green-100 text-green-700',
    'completed' => 'bg-blue-100 text-blue-700',
    'cancelled' => 'bg-red-100 text-red-700',
];

$urgencyBadgeColors = [
    'low' => 'bg-gray-100 text-gray-700',
    'medium' => 'bg-yellow-100 text-yellow-700',
    'high' => 'bg-red-100 text-red-700',
];

$timeLabels = [
    'morning' => 'Morning',
    'afternoon' => 'Afternoon',
    'evening' => 'Evening',
];

$allStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];

ob_start();
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Consultation Bookings</h2>
        <p class="text-sm text-gray-500 mt-1">Manage incoming consultation requests from customers.</p>
    </div>
    <a href="/admin/consultations/types" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Manage Types
    </a>
</div>

<!-- Status Filter Tabs -->
<div class="mb-6 flex flex-wrap gap-2">
    <a href="/admin/consultations"
       class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition border <?= empty($statusFilter) ? 'bg-indigo-600 border-indigo-500 text-white' : 'bg-white border-gray-200 shadow-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900' ?>">
        All
        <span class="ml-2 px-2 py-0.5 text-xs rounded-full <?= empty($statusFilter) ? 'bg-indigo-500 text-white' : 'bg-gray-100 text-gray-500' ?>"><?= $counts['all'] ?></span>
    </a>
    <?php foreach ($allStatuses as $status): ?>
    <a href="/admin/consultations?status=<?= $status ?>"
       class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition border <?= ($statusFilter ?? '') === $status ? 'bg-indigo-600 border-indigo-500 text-white' : 'bg-white border-gray-200 shadow-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900' ?>">
        <?= ucfirst($status) ?>
        <span class="ml-2 px-2 py-0.5 text-xs rounded-full <?= ($statusFilter ?? '') === $status ? 'bg-indigo-500 text-white' : 'bg-gray-100 text-gray-500' ?>"><?= $counts[$status] ?></span>
    </a>
    <?php endforeach; ?>
</div>

<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <?php if (empty($bookings)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <p class="text-gray-500">
                <?php if ($statusFilter): ?>
                    No <?= $statusFilter ?> bookings found.
                <?php else: ?>
                    No consultation bookings yet. Bookings will appear here when customers submit requests.
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preferred Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Urgency</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($bookings as $booking): ?>
                    <tr class="hover:bg-gray-50 transition-colors" x-data="{ open: false }">
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-indigo-600"><?= h($booking['booking_number']) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900"><?= h($booking['customer_name']) ?></div>
                            <div class="text-xs text-gray-500"><?= h($booking['customer_email']) ?></div>
                            <?php if (!empty($booking['company'])): ?>
                                <div class="text-xs text-gray-500"><?= h($booking['company']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600"><?= h($booking['type_name'] ?? 'N/A') ?></span>
                            <?php if (!empty($booking['duration_minutes'])): ?>
                                <div class="text-xs text-gray-500"><?= $booking['duration_minutes'] ?> min</div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-white"><?= $booking['preferred_date'] ? formatDate($booking['preferred_date']) : 'N/A' ?></div>
                            <div class="text-xs text-gray-500"><?= h($timeLabels[$booking['preferred_time']] ?? $booking['preferred_time'] ?? '') ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $urgencyBadgeColors[$booking['urgency']] ?? 'bg-gray-100 text-gray-700' ?>">
                                <?= ucfirst($booking['urgency'] ?? 'medium') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusBadgeColors[$booking['status']] ?? 'bg-gray-100 text-gray-700' ?>">
                                <?= ucfirst($booking['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?= formatDate($booking['created_at'], 'd M Y, H:i') ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click="open = !open" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Update
                            </button>
                        </td>
                    </tr>
                    <!-- Expandable update row -->
                    <tr x-show="open" x-cloak class="bg-gray-50">
                        <td colspan="8" class="px-6 py-4">
                            <form method="POST" action="/admin/consultations/update-status" class="flex flex-col md:flex-row items-start md:items-end gap-4">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= $booking['id'] ?>">

                                <?php if (!empty($booking['project_description'])): ?>
                                <div class="w-full md:w-auto flex-shrink-0">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Project Description</label>
                                    <p class="text-sm text-gray-600 bg-gray-700 rounded-lg p-3 max-w-md"><?= h($booking['project_description']) ?></p>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($booking['customer_phone'])): ?>
                                <div class="flex-shrink-0">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Phone</label>
                                    <p class="text-sm text-gray-600"><?= h($booking['customer_phone']) ?></p>
                                </div>
                                <?php endif; ?>

                                <div class="flex-shrink-0">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                                    <select name="status" class="px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                                        <option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="confirmed" <?= $booking['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                        <option value="completed" <?= $booking['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="cancelled" <?= $booking['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </div>

                                <div class="flex-1 min-w-0 w-full md:w-auto">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Admin Notes</label>
                                    <input type="text" name="admin_notes" value="<?= h($booking['admin_notes'] ?? '') ?>"
                                           class="w-full px-3 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"
                                           placeholder="Add a note...">
                                </div>

                                <div class="flex gap-2 flex-shrink-0">
                                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                                        Save
                                    </button>
                                    <button type="button" @click="open = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                                        Cancel
                                    </button>
                                </div>
                            </form>
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
