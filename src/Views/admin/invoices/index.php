<?php
$pageTitle = 'Invoices';
$currentPage = 'invoices';
$tenant = currentTenant();
ob_start();
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Invoices</h2>
        <p class="text-sm text-gray-500 mt-1">Create and manage standalone invoices</p>
    </div>
    <a href="/admin/invoices/create" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Invoice
    </a>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs font-medium text-gray-500 uppercase">Total</p>
        <p class="text-2xl font-bold text-gray-900 mt-1"><?= (int)$totalCount ?></p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs font-medium text-gray-500 uppercase">Drafts</p>
        <p class="text-2xl font-bold text-gray-600 mt-1"><?= (int)$draftCount ?></p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs font-medium text-gray-500 uppercase">Sent</p>
        <p class="text-2xl font-bold text-blue-600 mt-1"><?= (int)$sentCount ?></p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs font-medium text-gray-500 uppercase">Overdue</p>
        <p class="text-2xl font-bold text-red-600 mt-1"><?= (int)$overdueCount ?></p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs font-medium text-gray-500 uppercase">Outstanding</p>
        <p class="text-2xl font-bold text-orange-600 mt-1"><?= formatMoney($outstanding) ?></p>
    </div>
</div>

<!-- Status Filter -->
<div class="flex gap-2 mb-6 overflow-x-auto">
    <a href="/admin/invoices" class="px-4 py-2 text-sm font-medium rounded-lg transition <?= !$status ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' ?>">All</a>
    <a href="/admin/invoices?status=draft" class="px-4 py-2 text-sm font-medium rounded-lg transition <?= $status === 'draft' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' ?>">Draft</a>
    <a href="/admin/invoices?status=sent" class="px-4 py-2 text-sm font-medium rounded-lg transition <?= $status === 'sent' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' ?>">Sent</a>
    <a href="/admin/invoices?status=overdue" class="px-4 py-2 text-sm font-medium rounded-lg transition <?= $status === 'overdue' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' ?>">Overdue</a>
    <a href="/admin/invoices?status=paid" class="px-4 py-2 text-sm font-medium rounded-lg transition <?= $status === 'paid' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' ?>">Paid</a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <?php if (empty($invoices)): ?>
        <div class="p-12 text-center text-gray-500">
            <p class="text-lg mb-2">No invoices found.</p>
            <p class="text-sm">Create your first invoice to get started.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($invoices as $inv): ?>
                    <?php
                        $statusColors = [
                            'draft' => 'bg-gray-100 text-gray-700',
                            'sent' => 'bg-blue-100 text-blue-700',
                            'viewed' => 'bg-purple-100 text-purple-700',
                            'paid' => 'bg-green-100 text-green-700',
                            'partially_paid' => 'bg-yellow-100 text-yellow-700',
                            'overdue' => 'bg-red-100 text-red-700',
                            'cancelled' => 'bg-gray-100 text-gray-500',
                        ];
                        $badgeClass = $statusColors[$inv['status']] ?? 'bg-gray-100 text-gray-700';
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-indigo-600">
                            <a href="/admin/invoices/edit?id=<?= (int)$inv['id'] ?>" class="hover:underline"><?= h($inv['invoice_number']) ?></a>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900"><?= h($inv['customer_name']) ?></div>
                            <div class="text-xs text-gray-500"><?= h($inv['customer_email']) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badgeClass ?>">
                                <?= ucfirst(str_replace('_', ' ', $inv['status'])) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= formatMoney($inv['total_dkk']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500 <?= strtotime($inv['due_date']) < time() && !in_array($inv['status'], ['paid', 'cancelled', 'draft']) ? 'text-red-600 font-medium' : '' ?>">
                            <?= date('d/m/Y', strtotime($inv['due_date'])) ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?= formatMoney($inv['amount_paid_dkk']) ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="/admin/invoices/edit?id=<?= (int)$inv['id'] ?>" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
