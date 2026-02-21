<?php $pageTitle = 'ConnectPilot'; $currentPage = 'connectpilot'; $tenant = currentTenant(); ob_start(); ?>

<!-- Connection Status Card -->
<div class="mb-8">
    <?php if ($linkedinAccount && $linkedinAccount['status'] === 'active'): ?>
    <div class="bg-white border border-green-200 rounded-xl p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="p-3 bg-green-50 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-600">LinkedIn Connected</p>
                    <p class="text-gray-900 font-semibold"><?= h($linkedinAccount['linkedin_name']) ?></p>
                    <?php if ($linkedinAccount['linkedin_email']): ?>
                    <p class="text-sm text-gray-500"><?= h($linkedinAccount['linkedin_email']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <a href="/admin/connectpilot/konto" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                Manage Account
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white border border-red-200 rounded-xl p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="p-3 bg-red-50 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-red-600">LinkedIn Disconnected</p>
                    <p class="text-gray-600">Connect your LinkedIn account to start automating outreach.</p>
                </div>
            </div>
            <a href="/admin/connectpilot/konto" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                Connect Account
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Stats Row -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center">
            <div class="p-3 rounded-lg bg-indigo-50 text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Active Campaigns</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($activeCampaigns) ?></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center">
            <div class="p-3 rounded-lg bg-green-50 text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Total Leads</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($totalLeads) ?></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center">
            <div class="p-3 rounded-lg bg-blue-50 text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Connections Sent</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($totalConnectionsSent) ?></p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center">
            <div class="p-3 rounded-lg bg-purple-50 text-purple-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Messages Sent</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($totalMessagesSent) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Campaigns + Activity -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Campaigns -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Recent Campaigns</h3>
            <a href="/admin/connectpilot/kampagner" class="text-sm text-indigo-600 hover:text-indigo-500">View All</a>
        </div>
        <?php if (empty($recentCampaigns)): ?>
        <div class="p-8 text-center">
            <p class="text-gray-500 mb-3">No campaigns yet.</p>
            <a href="/admin/connectpilot/kampagner/opret" class="text-sm text-indigo-600 hover:text-indigo-500">Create your first campaign</a>
        </div>
        <?php else: ?>
        <div class="divide-y divide-gray-200">
            <?php foreach ($recentCampaigns as $campaign): ?>
            <div class="px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900"><?= h($campaign['name']) ?></p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        <?= number_format($campaign['leads_collected'] ?? 0) ?> leads &middot;
                        <?= number_format($campaign['leads_contacted'] ?? 0) ?> contacted
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <?php
                    $statusColors = [
                        'active' => 'bg-green-100 text-green-700',
                        'paused' => 'bg-yellow-100 text-yellow-700',
                        'draft' => 'bg-gray-100 text-gray-700',
                        'completed' => 'bg-blue-100 text-blue-700',
                    ];
                    $statusClass = $statusColors[$campaign['status']] ?? 'bg-gray-100 text-gray-700';
                    ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                        <?= ucfirst($campaign['status']) ?>
                    </span>
                    <a href="/admin/connectpilot/kampagner/rediger?id=<?= $campaign['id'] ?>" class="text-gray-500 hover:text-gray-900">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
        </div>
        <?php if (empty($recentActivity)): ?>
        <div class="p-8 text-center">
            <p class="text-gray-500">No activity yet. Start a campaign to see activity here.</p>
        </div>
        <?php else: ?>
        <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
            <?php foreach ($recentActivity as $activity): ?>
            <div class="px-6 py-3 flex items-start">
                <?php
                $typeIcons = [
                    'connection_sent' => ['icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z', 'color' => 'text-blue-600'],
                    'connection_accepted' => ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'text-green-600'],
                    'message_sent' => ['icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', 'color' => 'text-purple-600'],
                    'profile_viewed' => ['icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', 'color' => 'text-yellow-600'],
                    'reply_received' => ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'color' => 'text-indigo-600'],
                ];
                $typeInfo = $typeIcons[$activity['action_type'] ?? ''] ?? ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'color' => 'text-gray-500'];
                ?>
                <div class="flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4 <?= $typeInfo['color'] ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $typeInfo['icon'] ?>"/></svg>
                </div>
                <div class="ml-3 min-w-0 flex-1">
                    <p class="text-sm text-gray-600"><?= h($activity['description'] ?? $activity['action_type'] ?? '') ?></p>
                    <p class="text-xs text-gray-500 mt-0.5"><?= formatDate($activity['created_at'], 'd M Y H:i') ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
