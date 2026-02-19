<?php
use App\Models\Tenant;
use App\Models\User;
use App\Database\Database;

// Gather stats
$totalTenants = Tenant::count();
$activeTenants = Tenant::count('active');
$trialTenants = Tenant::count('trial');

// Total users across all tenants
$db = Database::getConnection();
$stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE tenant_id IS NOT NULL");
$stmt->execute();
$totalUsers = $stmt->fetch()['count'];

// Recent tenants
$stmt = $db->prepare("SELECT t.*, p.name as plan_name FROM tenants t LEFT JOIN plans p ON t.plan_id = p.id ORDER BY t.created_at DESC LIMIT 10");
$stmt->execute();
$recentTenants = $stmt->fetchAll();

$stats = [
    ['label' => 'Total Tenants', 'value' => $totalTenants, 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'color' => 'blue'],
    ['label' => 'Active Tenants', 'value' => $activeTenants, 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'green'],
    ['label' => 'Trial Tenants', 'value' => $trialTenants, 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'orange'],
    ['label' => 'Total Users', 'value' => $totalUsers, 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'color' => 'purple'],
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

<!-- Recent Tenants -->
<div class="bg-gray-800 rounded-xl border border-gray-700">
    <div class="px-6 py-4 border-b border-gray-700 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-white">Recent Tenants</h3>
        <a href="/tenants" class="text-sm text-indigo-400 hover:text-indigo-300">View all</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Slug</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Plan</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Created</th>
                    <th class="text-right px-6 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <?php if (empty($recentTenants)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">No tenants yet.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($recentTenants as $t): ?>
                <tr class="hover:bg-gray-700/50">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-white"><?= h($t['name']) ?></div>
                        <div class="text-xs text-gray-400"><?= h($t['email'] ?? '') ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-300"><?= h($t['slug']) ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <?php
                        $statusColors = [
                            'active' => 'bg-green-900 text-green-300',
                            'trial' => 'bg-yellow-900 text-yellow-300',
                            'suspended' => 'bg-red-900 text-red-300',
                            'cancelled' => 'bg-gray-700 text-gray-400',
                        ];
                        $statusClass = $statusColors[$t['status']] ?? 'bg-gray-700 text-gray-400';
                        ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>">
                            <?= ucfirst(h($t['status'])) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-300"><?= h($t['plan_name'] ?? 'None') ?></td>
                    <td class="px-6 py-4 text-sm text-gray-400"><?= formatDate($t['created_at']) ?></td>
                    <td class="px-6 py-4 text-right">
                        <a href="/tenants/edit?id=<?= $t['id'] ?>" class="text-indigo-400 hover:text-indigo-300 text-sm">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/superadmin/layouts/layout.php';
?>
