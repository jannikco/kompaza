<!-- Revenue summary -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <p class="text-sm text-gray-400 mb-1">Samlet omsætning</p>
        <p class="text-3xl font-bold text-green-400"><?= number_format($totalRevenue / 100, 2, ',', '.') ?> DKK</p>
    </div>
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <p class="text-sm text-gray-400 mb-1">Antal salg</p>
        <p class="text-3xl font-bold text-blue-400"><?= $totalSales ?></p>
    </div>
</div>

<!-- Purchases table -->
<div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-700">
        <h2 class="text-lg font-semibold text-white">Seneste salg</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-400 text-sm border-b border-gray-700">
                    <th class="px-6 py-3">Dato</th>
                    <th class="px-6 py-3">E-bog</th>
                    <th class="px-6 py-3">Kunde</th>
                    <th class="px-6 py-3">Beløb</th>
                    <th class="px-6 py-3">Gebyr</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($purchases)): ?>
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Ingen salg endnu.</td></tr>
                <?php else: ?>
                    <?php foreach ($purchases as $purchase): ?>
                    <tr class="border-b border-gray-700/50">
                        <td class="px-6 py-3 text-sm text-gray-300"><?= formatDate($purchase['created_at']) ?></td>
                        <td class="px-6 py-3 text-sm text-white"><?= h($purchase['ebook_title'] ?? '-') ?></td>
                        <td class="px-6 py-3 text-sm text-gray-300">
                            <?= h($purchase['customer_email'] ?? '-') ?>
                        </td>
                        <td class="px-6 py-3 text-sm text-white"><?= number_format($purchase['amount_cents'] / 100, 2, ',', '.') ?> DKK</td>
                        <td class="px-6 py-3 text-sm text-gray-400"><?= number_format($purchase['application_fee_cents'] / 100, 2, ',', '.') ?> DKK</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium
                                <?= $purchase['status'] === 'completed' ? 'bg-green-500/20 text-green-400' : ($purchase['status'] === 'pending' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400') ?>">
                                <?php
                                $purchaseStatusLabels = [
                                    'completed' => 'Gennemført',
                                    'pending' => 'Afventer',
                                    'failed' => 'Fejlet',
                                ];
                                echo $purchaseStatusLabels[$purchase['status']] ?? ucfirst($purchase['status']);
                                ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
