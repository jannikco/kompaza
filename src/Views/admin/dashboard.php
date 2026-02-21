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
$db = Database::getConnection();

// Time period toggle (7 or 30 days, default 7)
$period = in_array($_GET['period'] ?? '', ['7', '30']) ? (int)$_GET['period'] : 7;

// --- Period-filtered stats (4 main cards) ---

// Customers in period
$stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE tenant_id = ? AND role = 'customer' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
$stmt->execute([$tenantId, $period]);
$customerCount = $stmt->fetch()['count'];

$stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE tenant_id = ? AND role = 'customer' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
$stmt->execute([$tenantId, $period * 2, $period]);
$customerPrev = $stmt->fetch()['count'];
$customerGrowth = $customerPrev > 0 ? round((($customerCount - $customerPrev) / $customerPrev) * 100) : ($customerCount > 0 ? 100 : 0);

// Subscribers in period
$stmt = $db->prepare("SELECT COUNT(*) as count FROM email_signups WHERE tenant_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
$stmt->execute([$tenantId, $period]);
$subscriberCount = $stmt->fetch()['count'];

$stmt = $db->prepare("SELECT COUNT(*) as count FROM email_signups WHERE tenant_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
$stmt->execute([$tenantId, $period * 2, $period]);
$subscriberPrev = $stmt->fetch()['count'];
$subscriberGrowth = $subscriberPrev > 0 ? round((($subscriberCount - $subscriberPrev) / $subscriberPrev) * 100) : ($subscriberCount > 0 ? 100 : 0);

// Orders in period
$stmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE tenant_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
$stmt->execute([$tenantId, $period]);
$orderCount = $stmt->fetch()['count'];

$stmt = $db->prepare("SELECT COUNT(*) as count FROM orders WHERE tenant_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
$stmt->execute([$tenantId, $period * 2, $period]);
$orderPrev = $stmt->fetch()['count'];
$orderGrowth = $orderPrev > 0 ? round((($orderCount - $orderPrev) / $orderPrev) * 100) : ($orderCount > 0 ? 100 : 0);

// Revenue in period
$stmt = $db->prepare("SELECT COALESCE(SUM(total_dkk), 0) as total FROM orders WHERE tenant_id = ? AND status != 'cancelled' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
$stmt->execute([$tenantId, $period]);
$totalRevenue = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COALESCE(SUM(total_dkk), 0) as total FROM orders WHERE tenant_id = ? AND status != 'cancelled' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
$stmt->execute([$tenantId, $period * 2, $period]);
$revenuePrev = $stmt->fetch()['total'];
$revenueGrowth = $revenuePrev > 0 ? round((($totalRevenue - $revenuePrev) / $revenuePrev) * 100) : ($totalRevenue > 0 ? 100 : 0);

// --- Lifetime stats (feature row) ---
$articleCount = Article::countByTenant($tenantId);
$ebookCount = Ebook::countByTenant($tenantId);
$leadMagnetCount = LeadMagnet::countByTenant($tenantId);

$pendingOrders = Order::countByTenant($tenantId, 'pending');
$recentOrders = Order::recentByTenant($tenantId, 5);

// Course stats (if feature enabled)
$enrollmentCount = 0;
$courseCount = 0;
$certificateCount = 0;
if (tenantFeature('courses')) {
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

// Stats cards
$stats = [
    ['label' => 'Customers', 'value' => $customerCount, 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'color' => 'blue', 'trend' => $customerGrowth],
    ['label' => 'Subscribers', 'value' => $subscriberCount, 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'color' => 'green', 'trend' => $subscriberGrowth],
    ['label' => 'Orders', 'value' => $orderCount, 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', 'color' => 'purple', 'trend' => $orderGrowth],
    ['label' => 'Revenue', 'value' => formatMoney($totalRevenue), 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'emerald', 'raw' => true, 'trend' => $revenueGrowth],
];

$colorMap = [
    'blue' => 'bg-blue-50 text-blue-600',
    'green' => 'bg-green-50 text-green-600',
    'purple' => 'bg-purple-50 text-purple-600',
    'orange' => 'bg-orange-50 text-orange-600',
    'emerald' => 'bg-emerald-50 text-emerald-600',
    'cyan' => 'bg-cyan-50 text-cyan-600',
    'red' => 'bg-red-50 text-red-600',
    'indigo' => 'bg-indigo-50 text-indigo-600',
];

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
ob_start();
?>

<!-- Period Toggle -->
<div class="flex items-center gap-2 mb-6">
    <span class="text-sm text-gray-500">Showing:</span>
    <a href="?period=7" class="px-3 py-1.5 text-sm font-medium rounded-lg <?= $period === 7 ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' ?>">Last 7 days</a>
    <a href="?period=30" class="px-3 py-1.5 text-sm font-medium rounded-lg <?= $period === 30 ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' ?>">Last 30 days</a>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <?php foreach ($stats as $stat): ?>
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center">
            <div class="p-3 rounded-lg <?= $colorMap[$stat['color']] ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $stat['icon'] ?>"/></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500"><?= $stat['label'] ?></p>
                <p class="text-2xl font-bold text-gray-900"><?= !empty($stat['raw']) ? $stat['value'] : number_format($stat['value']) ?></p>
                <?php if (isset($stat['trend'])): ?>
                <p class="text-xs <?= $stat['trend'] >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                    <?= $stat['trend'] >= 0 ? '+' : '' ?><?= $stat['trend'] ?>% vs prev <?= $period ?>d
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Feature Stats Row -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm text-center">
        <p class="text-2xl font-bold text-gray-900"><?= number_format($articleCount) ?></p>
        <p class="text-xs text-gray-500 mt-1">Articles</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm text-center">
        <p class="text-2xl font-bold text-gray-900"><?= number_format($ebookCount) ?></p>
        <p class="text-xs text-gray-500 mt-1">Ebooks</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm text-center">
        <p class="text-2xl font-bold text-gray-900"><?= number_format($leadMagnetCount) ?></p>
        <p class="text-xs text-gray-500 mt-1">Lead Magnets</p>
    </div>
    <?php if (tenantFeature('courses')): ?>
    <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm text-center">
        <p class="text-2xl font-bold text-gray-900"><?= number_format($enrollmentCount) ?></p>
        <p class="text-xs text-gray-500 mt-1">Active Enrollments</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm text-center">
        <p class="text-2xl font-bold text-gray-900"><?= number_format($certificateCount) ?></p>
        <p class="text-xs text-gray-500 mt-1">Certificates Issued</p>
    </div>
    <?php endif; ?>
    <?php if (tenantFeature('consultations')): ?>
    <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm text-center">
        <p class="text-2xl font-bold text-<?= $pendingConsultations > 0 ? 'amber' : 'gray' ?>-600"><?= number_format($pendingConsultations) ?></p>
        <p class="text-xs text-gray-500 mt-1">Pending Bookings</p>
    </div>
    <?php endif; ?>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Recent Orders -->
    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
            <?php if ($pendingOrders > 0): ?>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700"><?= $pendingOrders ?> pending</span>
            <?php endif; ?>
        </div>
        <?php if (empty($recentOrders)): ?>
            <p class="text-sm text-gray-500">No orders yet.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($recentOrders as $order): ?>
                <a href="/admin/ordrer/vis?id=<?= $order['id'] ?>" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <div>
                        <p class="text-sm font-medium text-gray-900"><?= h($order['customer_name'] ?? 'Guest') ?></p>
                        <p class="text-xs text-gray-500">#<?= h($order['order_number']) ?> &middot; <?= formatDate($order['created_at']) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900"><?= formatMoney($order['total_dkk']) ?></p>
                        <?php
                        $statusColors = [
                            'pending' => 'text-amber-600',
                            'processing' => 'text-blue-600',
                            'completed' => 'text-green-600',
                            'cancelled' => 'text-red-600',
                        ];
                        ?>
                        <p class="text-xs <?= $statusColors[$order['status']] ?? 'text-gray-500' ?>"><?= ucfirst($order['status']) ?></p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <a href="/admin/ordrer" class="block mt-4 text-sm text-indigo-600 hover:text-indigo-500 text-center">View all orders &rarr;</a>
        <?php endif; ?>
    </div>

    <!-- Quick Actions + Your Site -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <?php if (tenantFeature('lead_magnets')): ?>
                <a href="/admin/lead-magnets/opret" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 border border-gray-200 transition">
                    <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span class="text-sm text-gray-700">New Lead Magnet</span>
                </a>
                <?php endif; ?>
                <?php if (tenantFeature('blog')): ?>
                <a href="/admin/artikler/opret" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 border border-gray-200 transition">
                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span class="text-sm text-gray-700">New Article</span>
                </a>
                <?php endif; ?>
                <a href="/admin/kunder/opret" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 border border-gray-200 transition">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span class="text-sm text-gray-700">New Customer</span>
                </a>
                <?php if (tenantFeature('orders')): ?>
                <a href="/admin/produkter/opret" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 border border-gray-200 transition">
                    <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span class="text-sm text-gray-700">New Product</span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Site</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Subdomain</span>
                    <a href="<?= tenantUrl() ?>" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-500"><?= h($tenant['slug']) ?>.kompaza.com</a>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Status</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $tenant['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                        <?= ucfirst($tenant['status']) ?>
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Plan</span>
                    <span class="text-sm text-gray-900"><?= h($tenant['subscription_status'] === 'trialing' ? 'Free Trial' : 'Active') ?></span>
                </div>
                <?php if ($tenant['trial_ends_at']): ?>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Trial Ends</span>
                    <span class="text-sm text-gray-900"><?= formatDate($tenant['trial_ends_at']) ?></span>
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
