<!-- Top row: 4 stat cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Revenue -->
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center">
            <div class="p-3 rounded-lg bg-green-900/50 text-green-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-400">Total Revenue</p>
                <p class="text-2xl font-bold text-white"><?= formatMoney($totalRevenue) ?></p>
            </div>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center">
            <div class="p-3 rounded-lg bg-blue-900/50 text-blue-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-400">Total Orders</p>
                <p class="text-2xl font-bold text-white"><?= number_format($totalOrders) ?></p>
            </div>
        </div>
    </div>

    <!-- Total Customers -->
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center">
            <div class="p-3 rounded-lg bg-purple-900/50 text-purple-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-400">Total Customers</p>
                <p class="text-2xl font-bold text-white"><?= number_format($customerCount) ?></p>
            </div>
        </div>
    </div>

    <!-- Email Subscribers -->
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center">
            <div class="p-3 rounded-lg bg-orange-900/50 text-orange-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-400">Email Subscribers</p>
                <p class="text-2xl font-bold text-white"><?= number_format($emailSignupCount) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Chart: last 30 days -->
<?php if (!empty($dailyRevenue)): ?>
<?php $maxRevenue = max(array_column($dailyRevenue, 'revenue')); ?>
<div class="bg-gray-800 rounded-xl border border-gray-700 p-6 mb-8">
    <h2 class="text-lg font-semibold text-white mb-4">Revenue - Last 30 Days</h2>
    <?php if ($maxRevenue > 0): ?>
    <div class="flex items-end gap-1" style="height: 200px;">
        <?php foreach ($dailyRevenue as $i => $day): ?>
        <?php
            $heightPercent = $maxRevenue > 0 ? ($day['revenue'] / $maxRevenue) * 100 : 0;
            $minHeight = $day['revenue'] > 0 ? max($heightPercent, 2) : 0;
        ?>
        <div class="flex-1 flex flex-col items-center justify-end h-full">
            <div
                class="w-full rounded-t bg-indigo-500 hover:bg-indigo-400 transition-colors cursor-pointer"
                style="height: <?= $minHeight ?>%;"
                title="<?= h($day['label']) ?>: <?= formatMoney($day['revenue']) ?>"
            ></div>
            <?php if ($i % 5 === 0): ?>
            <span class="text-[10px] text-gray-500 mt-1 whitespace-nowrap"><?= h($day['label']) ?></span>
            <?php else: ?>
            <span class="text-[10px] text-transparent mt-1">&nbsp;</span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="flex items-center justify-center h-40 text-gray-500">
        No revenue data for the last 30 days.
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Tabbed sections -->
<div x-data="{ activeTab: 'orders' }" class="bg-gray-800 rounded-xl border border-gray-700">
    <!-- Tab navigation -->
    <div class="border-b border-gray-700 px-6">
        <nav class="flex -mb-px space-x-6 overflow-x-auto">
            <button @click="activeTab = 'orders'"
                :class="activeTab === 'orders' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-600'"
                class="py-3 px-1 border-b-2 text-sm font-medium whitespace-nowrap transition-colors">
                Orders
            </button>
            <button @click="activeTab = 'ebooks'"
                :class="activeTab === 'ebooks' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-600'"
                class="py-3 px-1 border-b-2 text-sm font-medium whitespace-nowrap transition-colors">
                Ebook Sales
            </button>
            <?php if (tenantFeature('courses')): ?>
            <button @click="activeTab = 'courses'"
                :class="activeTab === 'courses' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-600'"
                class="py-3 px-1 border-b-2 text-sm font-medium whitespace-nowrap transition-colors">
                Courses
            </button>
            <button @click="activeTab = 'certificates'"
                :class="activeTab === 'certificates' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-600'"
                class="py-3 px-1 border-b-2 text-sm font-medium whitespace-nowrap transition-colors">
                Certificates
            </button>
            <?php endif; ?>
            <?php if (tenantFeature('consultations')): ?>
            <button @click="activeTab = 'consultations'"
                :class="activeTab === 'consultations' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-600'"
                class="py-3 px-1 border-b-2 text-sm font-medium whitespace-nowrap transition-colors">
                Consultations
            </button>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Tab: Orders -->
    <div x-show="activeTab === 'orders'" x-cloak>
        <div class="px-6 py-4 flex items-center justify-between border-b border-gray-700/50">
            <h3 class="text-base font-semibold text-white">Recent Orders</h3>
            <a href="/admin/sales/export-orders" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-gray-400 text-sm border-b border-gray-700">
                        <th class="px-6 py-3">Order #</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No orders yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr class="border-b border-gray-700/50 hover:bg-gray-700/30">
                            <td class="px-6 py-3 text-sm text-white font-medium"><?= h($order['order_number'] ?? '-') ?></td>
                            <td class="px-6 py-3 text-sm text-gray-300"><?= h($order['customer_name'] ?? '-') ?></td>
                            <td class="px-6 py-3 text-sm text-white"><?= formatMoney($order['total_dkk'] ?? 0) ?></td>
                            <td class="px-6 py-3">
                                <?php
                                $statusColors = [
                                    'pending' => 'bg-yellow-500/20 text-yellow-400',
                                    'processing' => 'bg-blue-500/20 text-blue-400',
                                    'shipped' => 'bg-indigo-500/20 text-indigo-400',
                                    'completed' => 'bg-green-500/20 text-green-400',
                                    'cancelled' => 'bg-red-500/20 text-red-400',
                                    'refunded' => 'bg-gray-500/20 text-gray-400',
                                ];
                                $statusColor = $statusColors[$order['status']] ?? 'bg-gray-500/20 text-gray-400';
                                ?>
                                <span class="px-2 py-0.5 rounded text-xs font-medium <?= $statusColor ?>">
                                    <?= ucfirst(h($order['status'] ?? 'unknown')) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-400"><?= formatDate($order['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab: Ebook Sales -->
    <div x-show="activeTab === 'ebooks'" x-cloak>
        <div class="px-6 py-4 flex items-center justify-between border-b border-gray-700/50">
            <h3 class="text-base font-semibold text-white">Ebook Purchases (<?= $ebookSalesCount ?> total, <?= formatMoney($ebookRevenue / 100) ?> revenue)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-gray-400 text-sm border-b border-gray-700">
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Ebook</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Amount</th>
                        <th class="px-6 py-3">Fee</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ebookPurchases)): ?>
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No ebook sales yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($ebookPurchases as $purchase): ?>
                        <tr class="border-b border-gray-700/50 hover:bg-gray-700/30">
                            <td class="px-6 py-3 text-sm text-gray-300"><?= formatDate($purchase['created_at']) ?></td>
                            <td class="px-6 py-3 text-sm text-white"><?= h($purchase['ebook_title'] ?? '-') ?></td>
                            <td class="px-6 py-3 text-sm text-gray-300"><?= h($purchase['customer_email'] ?? '-') ?></td>
                            <td class="px-6 py-3 text-sm text-white"><?= formatMoney(($purchase['amount_cents'] ?? 0) / 100) ?></td>
                            <td class="px-6 py-3 text-sm text-gray-400"><?= formatMoney(($purchase['application_fee_cents'] ?? 0) / 100) ?></td>
                            <td class="px-6 py-3">
                                <?php
                                $purchaseStatusColors = [
                                    'completed' => 'bg-green-500/20 text-green-400',
                                    'pending' => 'bg-yellow-500/20 text-yellow-400',
                                    'failed' => 'bg-red-500/20 text-red-400',
                                ];
                                $purchaseColor = $purchaseStatusColors[$purchase['status']] ?? 'bg-gray-500/20 text-gray-400';
                                ?>
                                <span class="px-2 py-0.5 rounded text-xs font-medium <?= $purchaseColor ?>">
                                    <?= ucfirst(h($purchase['status'] ?? 'unknown')) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab: Courses -->
    <?php if (tenantFeature('courses')): ?>
    <div x-show="activeTab === 'courses'" x-cloak>
        <div class="px-6 py-4 border-b border-gray-700/50">
            <h3 class="text-base font-semibold text-white">Course Statistics</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-gray-400 text-sm border-b border-gray-700">
                        <th class="px-6 py-3">Course</th>
                        <th class="px-6 py-3 text-center">Enrolled</th>
                        <th class="px-6 py-3 text-center">Completion Rate</th>
                        <th class="px-6 py-3 text-center">Quiz Pass Rate</th>
                        <th class="px-6 py-3 text-center">Certificates</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($courseStats)): ?>
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No courses yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($courseStats as $cs): ?>
                        <tr class="border-b border-gray-700/50 hover:bg-gray-700/30">
                            <td class="px-6 py-3 text-sm text-white font-medium"><?= h($cs['title']) ?></td>
                            <td class="px-6 py-3 text-sm text-gray-300 text-center"><?= number_format($cs['enrolled']) ?></td>
                            <td class="px-6 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-20 bg-gray-700 rounded-full h-2">
                                        <div class="bg-indigo-500 h-2 rounded-full" style="width: <?= min($cs['completion_rate'], 100) ?>%"></div>
                                    </div>
                                    <span class="text-sm text-gray-300"><?= $cs['completion_rate'] ?>%</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-20 bg-gray-700 rounded-full h-2">
                                        <div class="<?= $cs['quiz_pass_rate'] >= 70 ? 'bg-green-500' : ($cs['quiz_pass_rate'] >= 40 ? 'bg-yellow-500' : 'bg-red-500') ?> h-2 rounded-full" style="width: <?= min($cs['quiz_pass_rate'], 100) ?>%"></div>
                                    </div>
                                    <span class="text-sm text-gray-300"><?= $cs['quiz_pass_rate'] ?>%</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-300 text-center"><?= number_format($cs['certificates']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tab: Certificates -->
    <?php if (tenantFeature('courses')): ?>
    <div x-show="activeTab === 'certificates'" x-cloak>
        <div class="px-6 py-4 flex items-center justify-between border-b border-gray-700/50">
            <h3 class="text-base font-semibold text-white">Certificates Issued: <?= number_format($certificateCount) ?></h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-gray-400 text-sm border-b border-gray-700">
                        <th class="px-6 py-3">Certificate #</th>
                        <th class="px-6 py-3">Student</th>
                        <th class="px-6 py-3">Course</th>
                        <th class="px-6 py-3">Score</th>
                        <th class="px-6 py-3">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentCertificates)): ?>
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No certificates issued yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentCertificates as $cert): ?>
                        <tr class="border-b border-gray-700/50 hover:bg-gray-700/30">
                            <td class="px-6 py-3 text-sm text-white font-mono"><?= h($cert['certificate_number'] ?? '-') ?></td>
                            <td class="px-6 py-3 text-sm text-gray-300"><?= h($cert['user_name'] ?? '-') ?></td>
                            <td class="px-6 py-3 text-sm text-white"><?= h($cert['course_title'] ?? '-') ?></td>
                            <td class="px-6 py-3 text-sm text-gray-300">
                                <?php if (isset($cert['score_percentage'])): ?>
                                    <?= $cert['score_percentage'] ?>%
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-400"><?= formatDate($cert['issued_at'] ?? $cert['created_at'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tab: Consultations -->
    <?php if (tenantFeature('consultations')): ?>
    <div x-show="activeTab === 'consultations'" x-cloak>
        <div class="px-6 py-4 border-b border-gray-700/50">
            <h3 class="text-base font-semibold text-white">Consultation Bookings: <?= number_format($consultationCount) ?></h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Pending -->
                <div class="bg-gray-900 rounded-lg p-5 border border-gray-700">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-400">Pending</span>
                        <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                    </div>
                    <p class="text-3xl font-bold text-yellow-400"><?= number_format($consultationPending) ?></p>
                </div>
                <!-- Confirmed -->
                <div class="bg-gray-900 rounded-lg p-5 border border-gray-700">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-400">Confirmed</span>
                        <span class="w-3 h-3 rounded-full bg-blue-400"></span>
                    </div>
                    <p class="text-3xl font-bold text-blue-400"><?= number_format($consultationConfirmed) ?></p>
                </div>
                <!-- Completed -->
                <div class="bg-gray-900 rounded-lg p-5 border border-gray-700">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-400">Completed</span>
                        <span class="w-3 h-3 rounded-full bg-green-400"></span>
                    </div>
                    <p class="text-3xl font-bold text-green-400"><?= number_format($consultationCompleted) ?></p>
                </div>
            </div>
            <?php if ($consultationCount === 0): ?>
            <p class="text-center text-gray-500 mt-6">No consultation bookings yet.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
