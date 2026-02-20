<?php
use App\Models\Article;
use App\Models\Ebook;
use App\Models\LeadMagnet;
use App\Models\EmailSignup;
use App\Models\User;
use App\Models\Order;
use App\Models\Certificate;
use App\Models\ConsultationBooking;
use App\Database\Database;

$tenantId = currentTenantId();

// Core stats
$articleCount = Article::countByTenant($tenantId);
$ebookCount = Ebook::countByTenant($tenantId);
$leadMagnetCount = LeadMagnet::countByTenant($tenantId);
$customerCount = User::countByTenant($tenantId, 'customer');
$subscriberCount = EmailSignup::countByTenant($tenantId);

// Order stats
$orderCount = Order::countByTenant($tenantId);
$totalRevenue = Order::totalRevenueByTenant($tenantId);
$pendingOrders = Order::countByTenant($tenantId, 'pending');
$recentOrders = Order::recentByTenant($tenantId, 5);

// Course stats (if feature enabled)
$enrollmentCount = 0;
$courseCount = 0;
$certificateCount = 0;
if (tenantFeature('courses')) {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM course_enrollments ce JOIN courses c ON ce.course_id = c.id WHERE c.tenant_id = ? AND ce.status = 'active'");
    $stmt->execute([$tenantId]);
    $enrollmentCount = $stmt->fetch()['count'];

    $stmt = $db->prepare("SELECT COUNT(*) as count FROM courses WHERE tenant_id = ? AND status = 'published'");
    $stmt->execute([$tenantId]);
    $courseCount = $stmt->fetch()['count'];

    $certificateCount = Certificate::countByTenant($tenantId);
}

// Consultation stats (if feature enabled)
$pendingConsultations = 0;
if (tenantFeature('consultations')) {
    $pendingConsultations = ConsultationBooking::countByTenant($tenantId, 'pending');
}

// Subscriber growth: last 7 days vs previous 7 days
$db = Database::getConnection();
$stmt = $db->prepare("SELECT COUNT(*) as count FROM email_signups WHERE tenant_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stmt->execute([$tenantId]);
$subscribersLast7 = $stmt->fetch()['count'];

$stmt = $db->prepare("SELECT COUNT(*) as count FROM email_signups WHERE tenant_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stmt->execute([$tenantId]);
$subscribersPrev7 = $stmt->fetch()['count'];
$subscriberGrowth = $subscribersPrev7 > 0 ? round((($subscribersLast7 - $subscribersPrev7) / $subscribersPrev7) * 100) : ($subscribersLast7 > 0 ? 100 : 0);

// Stats cards
$stats = [
    ['label' => 'Customers', 'value' => $customerCount, 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'color' => 'blue'],
    ['label' => 'Subscribers', 'value' => $subscriberCount, 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'color' => 'green', 'trend' => $subscriberGrowth],
    ['label' => 'Orders', 'value' => $orderCount, 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', 'color' => 'purple'],
    ['label' => 'Revenue', 'value' => formatMoney($totalRevenue), 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'emerald', 'raw' => true],
];

$colorMap = [
    'blue' => 'bg-blue-900/50 text-blue-400',
    'green' => 'bg-green-900/50 text-green-400',
    'purple' => 'bg-purple-900/50 text-purple-400',
    'orange' => 'bg-orange-900/50 text-orange-400',
    'emerald' => 'bg-emerald-900/50 text-emerald-400',
    'cyan' => 'bg-cyan-900/50 text-cyan-400',
    'red' => 'bg-red-900/50 text-red-400',
    'indigo' => 'bg-indigo-900/50 text-indigo-400',
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
                <p class="text-2xl font-bold text-white"><?= !empty($stat['raw']) ? $stat['value'] : number_format($stat['value']) ?></p>
                <?php if (isset($stat['trend'])): ?>
                <p class="text-xs <?= $stat['trend'] >= 0 ? 'text-green-400' : 'text-red-400' ?>">
                    <?= $stat['trend'] >= 0 ? '+' : '' ?><?= $stat['trend'] ?>% last 7 days
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Feature Stats Row -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
        <p class="text-2xl font-bold text-white"><?= number_format($articleCount) ?></p>
        <p class="text-xs text-gray-400 mt-1">Articles</p>
    </div>
    <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
        <p class="text-2xl font-bold text-white"><?= number_format($ebookCount) ?></p>
        <p class="text-xs text-gray-400 mt-1">Ebooks</p>
    </div>
    <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
        <p class="text-2xl font-bold text-white"><?= number_format($leadMagnetCount) ?></p>
        <p class="text-xs text-gray-400 mt-1">Lead Magnets</p>
    </div>
    <?php if (tenantFeature('courses')): ?>
    <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
        <p class="text-2xl font-bold text-white"><?= number_format($enrollmentCount) ?></p>
        <p class="text-xs text-gray-400 mt-1">Active Enrollments</p>
    </div>
    <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
        <p class="text-2xl font-bold text-white"><?= number_format($certificateCount) ?></p>
        <p class="text-xs text-gray-400 mt-1">Certificates Issued</p>
    </div>
    <?php endif; ?>
    <?php if (tenantFeature('consultations')): ?>
    <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 text-center">
        <p class="text-2xl font-bold text-<?= $pendingConsultations > 0 ? 'amber' : 'white' ?>-400"><?= number_format($pendingConsultations) ?></p>
        <p class="text-xs text-gray-400 mt-1">Pending Bookings</p>
    </div>
    <?php endif; ?>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Recent Orders -->
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Recent Orders</h3>
            <?php if ($pendingOrders > 0): ?>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-900 text-amber-300"><?= $pendingOrders ?> pending</span>
            <?php endif; ?>
        </div>
        <?php if (empty($recentOrders)): ?>
            <p class="text-sm text-gray-500">No orders yet.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($recentOrders as $order): ?>
                <a href="/admin/ordrer/vis?id=<?= $order['id'] ?>" class="flex items-center justify-between p-3 bg-gray-700/50 rounded-lg hover:bg-gray-700 transition">
                    <div>
                        <p class="text-sm font-medium text-white"><?= h($order['customer_name'] ?? 'Guest') ?></p>
                        <p class="text-xs text-gray-400">#<?= h($order['order_number']) ?> &middot; <?= formatDate($order['created_at']) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-white"><?= formatMoney($order['total_dkk']) ?></p>
                        <?php
                        $statusColors = [
                            'pending' => 'text-amber-400',
                            'processing' => 'text-blue-400',
                            'completed' => 'text-green-400',
                            'cancelled' => 'text-red-400',
                        ];
                        ?>
                        <p class="text-xs <?= $statusColors[$order['status']] ?? 'text-gray-400' ?>"><?= ucfirst($order['status']) ?></p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <a href="/admin/ordrer" class="block mt-4 text-sm text-indigo-400 hover:text-indigo-300 text-center">View all orders &rarr;</a>
        <?php endif; ?>
    </div>

    <!-- Quick Actions + Your Site -->
    <div class="space-y-6">
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
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
