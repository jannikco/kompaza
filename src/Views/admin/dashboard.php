<?php
use App\Models\Article;
use App\Models\Ebook;
use App\Models\LeadMagnet;
use App\Models\EmailSignup;
use App\Models\User;
use App\Models\Order;
use App\Models\LinkedInLead;

$tenantId = currentTenantId();

$articleCount = Article::countByTenant($tenantId);
$ebookCount = Ebook::countByTenant($tenantId);
$leadMagnetCount = LeadMagnet::countByTenant($tenantId);
$customerCount = User::countByTenant($tenantId, 'customer');

// Stats cards data
$stats = [
    ['label' => 'Customers', 'value' => $customerCount, 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'color' => 'blue'],
    ['label' => 'Articles', 'value' => $articleCount, 'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z', 'color' => 'green'],
    ['label' => 'Lead Magnets', 'value' => $leadMagnetCount, 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'purple'],
    ['label' => 'Ebooks', 'value' => $ebookCount, 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'color' => 'orange'],
];

$colorMap = [
    'blue' => 'bg-blue-900/50 text-blue-400',
    'green' => 'bg-green-900/50 text-green-400',
    'purple' => 'bg-purple-900/50 text-purple-400',
    'orange' => 'bg-orange-900/50 text-orange-400',
];

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
ob_start();
?>

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <?php foreach ($stats as $stat): ?>
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center">
            <div class="p-3 rounded-lg <?= $colorMap[$stat['color']] ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $stat['icon'] ?>"/></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-400"><?= $stat['label'] ?></p>
                <p class="text-2xl font-bold text-white"><?= number_format($stat['value']) ?></p>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h3 class="text-lg font-semibold text-white mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 gap-3">
            <?php if (tenantFeature('lead_magnets')): ?>
            <a href="/admin/lead-magnets/opret" class="flex items-center p-3 bg-gray-700 rounded-lg hover:bg-gray-600 transition">
                <svg class="w-5 h-5 text-indigo-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="text-sm text-gray-200">New Lead Magnet</span>
            </a>
            <?php endif; ?>
            <?php if (tenantFeature('blog')): ?>
            <a href="/admin/artikler/opret" class="flex items-center p-3 bg-gray-700 rounded-lg hover:bg-gray-600 transition">
                <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="text-sm text-gray-200">New Article</span>
            </a>
            <?php endif; ?>
            <a href="/admin/kunder/opret" class="flex items-center p-3 bg-gray-700 rounded-lg hover:bg-gray-600 transition">
                <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="text-sm text-gray-200">New Customer</span>
            </a>
            <?php if (tenantFeature('orders')): ?>
            <a href="/admin/produkter/opret" class="flex items-center p-3 bg-gray-700 rounded-lg hover:bg-gray-600 transition">
                <svg class="w-5 h-5 text-purple-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="text-sm text-gray-200">New Product</span>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h3 class="text-lg font-semibold text-white mb-4">Your Site</h3>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-400">Subdomain</span>
                <a href="<?= tenantUrl() ?>" target="_blank" class="text-sm text-indigo-400 hover:text-indigo-300"><?= h($tenant['slug']) ?>.kompaza.com</a>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-400">Status</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $tenant['status'] === 'active' ? 'bg-green-900 text-green-300' : 'bg-yellow-900 text-yellow-300' ?>">
                    <?= ucfirst($tenant['status']) ?>
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-400">Plan</span>
                <span class="text-sm text-white"><?= h($tenant['subscription_status'] === 'trialing' ? 'Free Trial' : 'Active') ?></span>
            </div>
            <?php if ($tenant['trial_ends_at']): ?>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-400">Trial Ends</span>
                <span class="text-sm text-white"><?= formatDate($tenant['trial_ends_at']) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
