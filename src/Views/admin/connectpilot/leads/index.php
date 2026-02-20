<?php $pageTitle = 'Leads'; $currentPage = 'leadshark-leads'; $tenant = currentTenant(); ob_start(); ?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-white">Leads</h2>
        <p class="text-sm text-gray-400 mt-1"><?= number_format($totalLeads) ?> total leads collected.</p>
    </div>
</div>

<!-- Filters -->
<div class="bg-gray-800 border border-gray-700 rounded-xl p-4 mb-6">
    <form method="GET" action="/admin/leadshark/leads" class="flex flex-col sm:flex-row items-stretch sm:items-end gap-4">
        <div class="flex-1">
            <label for="search" class="block text-sm font-medium text-gray-300 mb-1">Search</label>
            <input type="text" name="search" id="search" value="<?= h($search) ?>"
                class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                placeholder="Search by name, company, or job title...">
        </div>
        <div class="sm:w-56">
            <label for="campaign_id" class="block text-sm font-medium text-gray-300 mb-1">Campaign</label>
            <select name="campaign_id" id="campaign_id"
                class="w-full px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                <option value="">All Campaigns</option>
                <?php foreach ($campaigns as $c): ?>
                <option value="<?= $c['id'] ?>" <?= (string)$campaignId === (string)$c['id'] ? 'selected' : '' ?>>
                    <?= h($c['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex items-end space-x-2">
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                Filter
            </button>
            <?php if ($search || $campaignId): ?>
            <a href="/admin/leadshark/leads" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 text-sm font-medium rounded-lg transition">
                Clear
            </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Leads Table -->
<div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
    <?php if (empty($leads)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <p class="text-gray-400 mb-2">No leads found.</p>
            <?php if ($search || $campaignId): ?>
            <p class="text-sm text-gray-500">Try adjusting your filters or search terms.</p>
            <?php else: ?>
            <p class="text-sm text-gray-500">Leads will appear here once your campaigns start collecting them.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Lead</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Company</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Campaign</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Added</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($leads as $lead): ?>
                    <tr class="hover:bg-gray-750">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-9 h-9 rounded-full bg-gray-700 flex items-center justify-center">
                                    <?php if (!empty($lead['profile_image_url'])): ?>
                                    <img src="<?= h($lead['profile_image_url']) ?>" alt="" class="w-9 h-9 rounded-full object-cover">
                                    <?php else: ?>
                                    <span class="text-sm font-medium text-gray-400"><?= strtoupper(substr($lead['full_name'] ?? '?', 0, 1)) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-white"><?= h($lead['full_name']) ?></p>
                                    <?php if ($lead['job_title']): ?>
                                    <p class="text-xs text-gray-400 truncate max-w-xs"><?= h($lead['job_title']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-300"><?= h($lead['company'] ?? '-') ?></td>
                        <td class="px-6 py-4">
                            <?php
                            $leadStatusColors = [
                                'new' => 'bg-gray-700 text-gray-300',
                                'contacted' => 'bg-blue-900 text-blue-300',
                                'connected' => 'bg-green-900 text-green-300',
                                'responded' => 'bg-purple-900 text-purple-300',
                                'converted' => 'bg-indigo-900 text-indigo-300',
                                'rejected' => 'bg-red-900 text-red-300',
                            ];
                            $leadStatusClass = $leadStatusColors[$lead['status'] ?? 'new'] ?? 'bg-gray-700 text-gray-300';
                            ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $leadStatusClass ?>">
                                <?= ucfirst($lead['status'] ?? 'New') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-400">
                            <?php
                            // Find campaign name
                            $campaignName = '-';
                            if ($lead['campaign_id']) {
                                foreach ($campaigns as $c) {
                                    if ((int)$c['id'] === (int)$lead['campaign_id']) {
                                        $campaignName = $c['name'];
                                        break;
                                    }
                                }
                            }
                            ?>
                            <?= h(truncate($campaignName, 30)) ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-400"><?= h($lead['location'] ?? '-') ?></td>
                        <td class="px-6 py-4 text-sm text-gray-400"><?= formatDate($lead['created_at']) ?></td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <?php if (!empty($lead['linkedin_url'])): ?>
                                <a href="<?= h($lead['linkedin_url']) ?>" target="_blank" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition" title="View on LinkedIn">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                                <?php endif; ?>
                                <?php if ($lead['email']): ?>
                                <a href="mailto:<?= h($lead['email']) ?>" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition" title="Send Email">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="px-6 py-4 border-t border-gray-700 flex items-center justify-between">
            <p class="text-sm text-gray-400">
                Page <?= $page ?> of <?= $totalPages ?>
            </p>
            <div class="flex items-center space-x-2">
                <?php if ($page > 1): ?>
                <a href="/admin/leadshark/leads?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $campaignId ? '&campaign_id=' . $campaignId : '' ?>"
                    class="px-3 py-1.5 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                    Previous
                </a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                <a href="/admin/leadshark/leads?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $campaignId ? '&campaign_id=' . $campaignId : '' ?>"
                    class="px-3 py-1.5 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                    Next
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
