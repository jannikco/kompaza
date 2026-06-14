<?php
$pageTitle = 'Analytics';
$currentPage = 'analytics';
ob_start();
?>

<!-- Period Toggle -->
<div class="flex items-center gap-2 mb-6">
    <span class="text-sm text-gray-500">Period:</span>
    <a href="?period=7" class="px-3 py-1.5 text-sm font-medium rounded-lg <?= $period === 7 ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' ?>">7 days</a>
    <a href="?period=30" class="px-3 py-1.5 text-sm font-medium rounded-lg <?= $period === 30 ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' ?>">30 days</a>
    <a href="?period=90" class="px-3 py-1.5 text-sm font-medium rounded-lg <?= $period === 90 ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' ?>">90 days</a>
</div>

<!-- Key Metrics -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center">
            <div class="p-3 rounded-lg bg-emerald-50 text-emerald-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Monthly Revenue (MRR)</p>
                <p class="text-2xl font-bold text-gray-900"><?= formatMoney($mrr) ?></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center">
            <div class="p-3 rounded-lg bg-blue-50 text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Customer Lifetime Value</p>
                <p class="text-2xl font-bold text-gray-900"><?= formatMoney($clv) ?></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center">
            <div class="p-3 rounded-lg <?= $churnRate > 20 ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Churn Rate (90 day)</p>
                <p class="text-2xl font-bold text-gray-900"><?= $churnRate ?>%</p>
            </div>
        </div>
    </div>
</div>

<!-- Conversion Funnel -->
<div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Conversion Funnel (last <?= $period ?> days)</h3>
    <div class="grid grid-cols-4 gap-4">
        <?php
        $funnelSteps = [
            ['label' => 'Page Views', 'value' => $funnel['views'], 'color' => 'indigo'],
            ['label' => 'Email Signups', 'value' => $funnel['signups'], 'color' => 'blue'],
            ['label' => 'Customers', 'value' => $funnel['customers'], 'color' => 'green'],
            ['label' => 'Orders', 'value' => $funnel['orders'], 'color' => 'emerald'],
        ];
        foreach ($funnelSteps as $i => $step):
            $prevValue = $i > 0 ? $funnelSteps[$i-1]['value'] : $step['value'];
            $convRate = $prevValue > 0 ? round($step['value'] / $prevValue * 100, 1) : 0;
        ?>
        <div class="text-center">
            <div class="bg-<?= $step['color'] ?>-50 rounded-lg p-4 mb-2">
                <p class="text-2xl font-bold text-<?= $step['color'] ?>-600"><?= number_format($step['value']) ?></p>
            </div>
            <p class="text-xs font-medium text-gray-700"><?= $step['label'] ?></p>
            <?php if ($i > 0): ?>
            <p class="text-xs text-gray-500"><?= $convRate ?>% from prev</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Revenue by Month -->
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Revenue</h3>
        <?php if (empty($revenueByMonth)): ?>
        <p class="text-sm text-gray-500">No revenue data yet.</p>
        <?php else: ?>
        <div class="space-y-2">
            <?php
            $maxRevenue = max(array_column($revenueByMonth, 'revenue')) ?: 1;
            foreach ($revenueByMonth as $m):
                $pct = round($m['revenue'] / $maxRevenue * 100);
            ?>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-500 w-16"><?= $m['month'] ?></span>
                <div class="flex-1 bg-gray-100 rounded-full h-5">
                    <div class="bg-indigo-500 h-5 rounded-full flex items-center justify-end pr-2" style="width: <?= max($pct, 5) ?>%">
                        <span class="text-xs text-white font-medium"><?= formatMoney($m['revenue']) ?></span>
                    </div>
                </div>
                <span class="text-xs text-gray-500 w-12 text-right"><?= $m['order_count'] ?> ord.</span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Revenue by Product -->
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue by Product</h3>
        <?php if (empty($revenueByProduct)): ?>
        <p class="text-sm text-gray-500">No product revenue data yet.</p>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($revenueByProduct as $p): ?>
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate"><?= h($p['product_name']) ?></p>
                    <p class="text-xs text-gray-500"><?= number_format($p['order_count']) ?> orders</p>
                </div>
                <span class="text-sm font-semibold text-gray-900 ml-4"><?= formatMoney($p['revenue']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Top Pages -->
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Pages</h3>
        <?php if (empty($topPages)): ?>
        <p class="text-sm text-gray-500">No page view data yet. Page tracking will start once the tracking snippet is active.</p>
        <?php else: ?>
        <div class="space-y-2">
            <?php foreach ($topPages as $page): ?>
            <div class="flex items-center justify-between py-1.5">
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-700 truncate"><?= h($page['page_url']) ?></p>
                    <p class="text-xs text-gray-400"><?= h($page['page_type']) ?></p>
                </div>
                <div class="text-right ml-4">
                    <span class="text-sm font-medium text-gray-900"><?= number_format($page['views']) ?></span>
                    <span class="text-xs text-gray-500 ml-1">(<?= number_format($page['unique_visitors']) ?> unique)</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Traffic Sources -->
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Traffic Sources</h3>
        <?php if (empty($trafficSources)): ?>
        <p class="text-sm text-gray-500">No traffic source data yet.</p>
        <?php else: ?>
        <div class="space-y-2">
            <?php
            $totalTraffic = array_sum(array_column($trafficSources, 'views')) ?: 1;
            foreach ($trafficSources as $src):
                $pct = round($src['views'] / $totalTraffic * 100, 1);
            ?>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-700 w-32 truncate"><?= h($src['source']) ?></span>
                <div class="flex-1 bg-gray-100 rounded-full h-4">
                    <div class="bg-blue-500 h-4 rounded-full" style="width: <?= max($pct, 3) ?>%"></div>
                </div>
                <span class="text-xs text-gray-500 w-20 text-right"><?= number_format($src['views']) ?> (<?= $pct ?>%)</span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Daily Growth Chart (simple bar visualization) -->
<div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm mb-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Daily New Customers & Subscribers</h3>
    <?php
    $customersByDay = [];
    foreach ($dailyGrowth['customers'] as $d) $customersByDay[$d['date']] = (int)$d['count'];
    $subscribersByDay = [];
    foreach ($dailyGrowth['subscribers'] as $d) $subscribersByDay[$d['date']] = (int)$d['count'];

    $allDays = [];
    $start = new DateTime("-{$period} days");
    $end = new DateTime();
    while ($start <= $end) {
        $allDays[] = $start->format('Y-m-d');
        $start->modify('+1 day');
    }

    $maxDaily = 1;
    foreach ($allDays as $day) {
        $maxDaily = max($maxDaily, $customersByDay[$day] ?? 0, $subscribersByDay[$day] ?? 0);
    }
    ?>
    <div class="flex items-end gap-1" style="height: 120px">
        <?php foreach ($allDays as $day):
            $cust = $customersByDay[$day] ?? 0;
            $subs = $subscribersByDay[$day] ?? 0;
            $custH = $maxDaily > 0 ? round($cust / $maxDaily * 100) : 0;
            $subsH = $maxDaily > 0 ? round($subs / $maxDaily * 100) : 0;
        ?>
        <div class="flex-1 flex flex-col items-center gap-0.5 group relative" title="<?= $day ?>: <?= $cust ?> customers, <?= $subs ?> subscribers">
            <div class="w-full bg-blue-400 rounded-t" style="height: <?= max($custH, 2) ?>%"></div>
            <div class="w-full bg-green-400 rounded-t" style="height: <?= max($subsH, 2) ?>%"></div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="flex justify-between mt-2">
        <span class="text-xs text-gray-400"><?= $allDays[0] ?? '' ?></span>
        <span class="text-xs text-gray-400"><?= end($allDays) ?: '' ?></span>
    </div>
    <div class="flex items-center gap-4 mt-3">
        <span class="flex items-center gap-1 text-xs text-gray-500"><span class="w-3 h-3 bg-blue-400 rounded inline-block"></span> Customers</span>
        <span class="flex items-center gap-1 text-xs text-gray-500"><span class="w-3 h-3 bg-green-400 rounded inline-block"></span> Subscribers</span>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
