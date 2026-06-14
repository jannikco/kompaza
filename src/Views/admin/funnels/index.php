<?php
$pageTitle = 'Sales Funnels';
$currentPage = 'funnels';
ob_start();

$typeLabels = ['optin' => 'Opt-in', 'sales' => 'Sales', 'webinar' => 'Webinar', 'launch' => 'Launch'];
$typeColors = ['optin' => 'bg-blue-100 text-blue-700', 'sales' => 'bg-green-100 text-green-700', 'webinar' => 'bg-purple-100 text-purple-700', 'launch' => 'bg-amber-100 text-amber-700'];
$statusColors = ['draft' => 'bg-gray-100 text-gray-700', 'active' => 'bg-green-100 text-green-700', 'paused' => 'bg-yellow-100 text-yellow-700', 'archived' => 'bg-red-100 text-red-700'];
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <p class="text-sm text-gray-500">Build multi-step funnels connecting your pages, products, and email sequences.</p>
    </div>
    <a href="/admin/funnels/create" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Funnel
    </a>
</div>

<?php if (empty($funnels)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
    <h3 class="text-lg font-medium text-gray-900 mb-2">No funnels yet</h3>
    <p class="text-gray-500 mb-4">Create your first funnel to connect your landing pages, products, and email sequences into a conversion flow.</p>
    <a href="/admin/funnels/create" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">Create Funnel</a>
</div>
<?php else: ?>
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Funnel</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Steps</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Views</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Conv. Rate</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php foreach ($funnels as $f): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900"><?= h($f['name']) ?></div>
                    <div class="text-xs text-gray-500">/funnel/<?= h($f['slug']) ?></div>
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium <?= $typeColors[$f['funnel_type']] ?? 'bg-gray-100 text-gray-700' ?>">
                        <?= $typeLabels[$f['funnel_type']] ?? ucfirst($f['funnel_type']) ?>
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium <?= $statusColors[$f['status']] ?? 'bg-gray-100 text-gray-700' ?>">
                        <?= ucfirst($f['status']) ?>
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900"><?= (int)$f['step_count'] ?></td>
                <td class="px-6 py-4 text-sm text-gray-900"><?= number_format($f['total_views']) ?></td>
                <td class="px-6 py-4 text-sm text-gray-900"><?= $f['conversion_rate'] ?>%</td>
                <td class="px-6 py-4 text-right">
                    <a href="/admin/funnels/edit?id=<?= $f['id'] ?>" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</a>
                    <form method="POST" action="/admin/funnels/delete" class="inline ml-3" onsubmit="return confirm('Delete this funnel?')">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="id" value="<?= $f['id'] ?>">
                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
