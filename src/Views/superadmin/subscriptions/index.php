<!-- Status summary -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <?php
    $statuses = [
        'active' => ['label' => 'Aktive', 'color' => 'green'],
        'trialing' => ['label' => 'Prøveperiode', 'color' => 'blue'],
        'past_due' => ['label' => 'Forfaldne', 'color' => 'yellow'],
        'canceled' => ['label' => 'Annullerede', 'color' => 'red'],
    ];
    foreach ($statuses as $status => $info):
    ?>
    <div class="bg-gray-800 rounded-xl p-4 border border-gray-700">
        <p class="text-sm text-gray-400"><?= $info['label'] ?></p>
        <p class="text-2xl font-bold text-<?= $info['color'] ?>-400"><?= $statusCounts[$status] ?? 0 ?></p>
    </div>
    <?php endforeach; ?>
</div>

<!-- Subscriptions table -->
<div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-400 text-sm border-b border-gray-700">
                    <th class="px-6 py-3">Tenant</th>
                    <th class="px-6 py-3">Plan</th>
                    <th class="px-6 py-3">Interval</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Periode slut</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($subscriptions)): ?>
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Ingen abonnementer endnu.</td></tr>
                <?php else: ?>
                    <?php foreach ($subscriptions as $sub): ?>
                    <tr class="border-b border-gray-700/50">
                        <td class="px-6 py-3 text-sm text-white"><?= h($sub['tenant_name'] ?? '-') ?></td>
                        <td class="px-6 py-3 text-sm text-gray-300"><?= h($sub['plan_name'] ?? '-') ?></td>
                        <td class="px-6 py-3 text-sm text-gray-300"><?= $sub['billing_interval'] === 'annual' ? 'Årligt' : 'Månedligt' ?></td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium
                                <?php if ($sub['status'] === 'active'): ?>bg-green-500/20 text-green-400
                                <?php elseif ($sub['status'] === 'trialing'): ?>bg-blue-500/20 text-blue-400
                                <?php elseif ($sub['status'] === 'past_due'): ?>bg-yellow-500/20 text-yellow-400
                                <?php else: ?>bg-red-500/20 text-red-400<?php endif; ?>">
                                <?= ucfirst($sub['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-400"><?= $sub['current_period_end'] ? formatDate($sub['current_period_end']) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
