<!-- MRR card -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <p class="text-sm text-gray-400 mb-1">Månedlig tilbagevendende omsætning (MRR)</p>
        <p class="text-3xl font-bold text-green-400">$<?= number_format($mrr / 100, 0) ?></p>
    </div>
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <p class="text-sm text-gray-400 mb-1">Aktive abonnementer</p>
        <p class="text-3xl font-bold text-blue-400"><?= ($statusCounts['active'] ?? 0) + ($statusCounts['trialing'] ?? 0) ?></p>
    </div>
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <p class="text-sm text-gray-400 mb-1">Årlig estimeret omsætning (ARR)</p>
        <p class="text-3xl font-bold text-purple-400">$<?= number_format(($mrr * 12) / 100, 0) ?></p>
    </div>
</div>

<!-- By plan -->
<div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
    <h2 class="text-lg font-semibold text-white mb-4">Abonnenter pr. plan</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <?php foreach ($plans as $plan): ?>
        <div class="flex items-center justify-between bg-gray-700/50 rounded-lg p-4">
            <span class="text-gray-300"><?= h($plan['name']) ?></span>
            <span class="text-white font-bold text-lg"><?= $planCounts[$plan['slug']] ?? 0 ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Status breakdown -->
<div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
    <h2 class="text-lg font-semibold text-white mb-4">Status oversigt</h2>
    <div class="space-y-3">
        <?php
        $allStatuses = [
            'active' => ['label' => 'Aktive', 'color' => 'green'],
            'trialing' => ['label' => 'Prøveperiode', 'color' => 'blue'],
            'past_due' => ['label' => 'Forfaldne', 'color' => 'yellow'],
            'canceled' => ['label' => 'Annullerede', 'color' => 'red'],
            'unpaid' => ['label' => 'Ubetalte', 'color' => 'red'],
        ];
        $total = array_sum($statusCounts) ?: 1;
        foreach ($allStatuses as $status => $info):
            $count = $statusCounts[$status] ?? 0;
            $pct = round(($count / $total) * 100);
        ?>
        <div>
            <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-400"><?= $info['label'] ?></span>
                <span class="text-gray-300"><?= $count ?></span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-2">
                <div class="bg-<?= $info['color'] ?>-500 h-2 rounded-full" style="width: <?= $pct ?>%"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
