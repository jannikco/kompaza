<?php
$pageTitle = 'Certificates';
$currentPage = 'certificates';
$tenant = currentTenant();
ob_start();
?>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-white">Certificates</h2>
    <span class="text-sm text-gray-400"><?= count($certificates) ?> total</span>
</div>

<?php if (empty($certificates)): ?>
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-12 text-center">
        <p class="text-gray-400">No certificates issued yet.</p>
    </div>
<?php else: ?>
    <div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-750 border-b border-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-400 font-medium">Certificate #</th>
                    <th class="px-4 py-3 text-left text-gray-400 font-medium">Student</th>
                    <th class="px-4 py-3 text-left text-gray-400 font-medium">Course</th>
                    <th class="px-4 py-3 text-left text-gray-400 font-medium">Score</th>
                    <th class="px-4 py-3 text-left text-gray-400 font-medium">Issued</th>
                    <th class="px-4 py-3 text-left text-gray-400 font-medium">Status</th>
                    <th class="px-4 py-3 text-right text-gray-400 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <?php foreach ($certificates as $cert): ?>
                <tr class="hover:bg-gray-750">
                    <td class="px-4 py-3 text-white font-mono text-xs"><?= h($cert['certificate_number']) ?></td>
                    <td class="px-4 py-3">
                        <p class="text-white"><?= h($cert['user_name']) ?></p>
                        <p class="text-xs text-gray-400"><?= h($cert['user_email']) ?></p>
                    </td>
                    <td class="px-4 py-3 text-gray-300"><?= h($cert['course_title'] ?? '') ?></td>
                    <td class="px-4 py-3 text-gray-300"><?= $cert['score_percentage'] ? number_format($cert['score_percentage'], 1) . '%' : '-' ?></td>
                    <td class="px-4 py-3 text-gray-300"><?= formatDate($cert['issued_at']) ?></td>
                    <td class="px-4 py-3">
                        <?php if ($cert['revoked_at']): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-900/50 text-red-400">Revoked</span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-900/50 text-green-400">Active</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <?php if (!$cert['revoked_at']): ?>
                        <div x-data="{ showRevoke: false }">
                            <button @click="showRevoke = true" class="text-red-400 hover:text-red-300 text-xs">Revoke</button>
                            <div x-show="showRevoke" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" @click.self="showRevoke = false">
                                <form method="POST" action="/admin/certificates/revoke" class="bg-gray-800 border border-gray-700 rounded-xl p-6 max-w-md w-full mx-4">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= $cert['id'] ?>">
                                    <h3 class="text-lg font-semibold text-white mb-4">Revoke Certificate</h3>
                                    <p class="text-sm text-gray-400 mb-4">This will permanently revoke certificate #<?= h($cert['certificate_number']) ?></p>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-300 mb-1.5">Reason (optional)</label>
                                        <input type="text" name="reason" class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg text-sm" placeholder="Reason for revocation...">
                                    </div>
                                    <div class="flex justify-end space-x-3">
                                        <button type="button" @click="showRevoke = false" class="px-4 py-2 text-gray-400 hover:text-white text-sm">Cancel</button>
                                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg">Revoke</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
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
