<?php
$pageTitle = 'Upsells & Downsells';
$currentPage = 'upsells';
$tenant = currentTenant();
ob_start();
?>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Upsells & Downsells</h2>
        <p class="text-sm text-gray-500 mt-1">Post-purchase offers shown after checkout to maximize revenue per customer.</p>
    </div>
    <a href="/admin/upsells/create" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Offer
    </a>
</div>

<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <?php if (empty($offers)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            <p class="text-gray-500 mb-4">No upsell or downsell offers yet. Create your first to boost post-purchase revenue.</p>
            <a href="/admin/upsells/create" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Add Offer</a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Offer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conversion</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($offers as $offer): ?>
                    <tr class="hover:bg-gray-50 transition-colors" x-data="{ confirmDelete: false }">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900"><?= h($offer['name']) ?></div>
                            <?php if ($offer['headline']): ?>
                                <div class="text-xs text-gray-500 mt-0.5 truncate max-w-xs"><?= h($offer['headline']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($offer['offer_type'] === 'upsell'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Upsell</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">Downsell</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= h($offer['product_name']) ?></td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900"><?= formatMoney($offer['offer_price_dkk']) ?></div>
                            <?php if ($offer['original_price_dkk']): ?>
                                <div class="text-xs text-gray-500 line-through"><?= formatMoney($offer['original_price_dkk']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php $rate = $offer['times_shown'] > 0 ? round(($offer['times_accepted'] / $offer['times_shown']) * 100, 1) : 0; ?>
                            <div class="text-sm text-gray-900"><?= $rate ?>%</div>
                            <div class="text-xs text-gray-500"><?= (int)$offer['times_accepted'] ?> / <?= (int)$offer['times_shown'] ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($offer['status'] === 'active'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="/admin/upsells/edit?id=<?= $offer['id'] ?>" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Edit</a>
                                <template x-if="!confirmDelete">
                                    <button @click="confirmDelete = true" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-gray-100 hover:bg-red-50 rounded-lg transition">Delete</button>
                                </template>
                                <template x-if="confirmDelete">
                                    <form method="POST" action="/admin/upsells/delete" class="inline-flex items-center space-x-1">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $offer['id'] ?>">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">Confirm</button>
                                        <button type="button" @click="confirmDelete = false" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Cancel</button>
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

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
