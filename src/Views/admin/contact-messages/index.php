<?php
$pageTitle = 'Contact Messages';
$currentPage = 'contact-messages';
$tenant = currentTenant();
ob_start();
?>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Contact Messages</h2>
    <span class="text-sm text-gray-500"><?= count($messages) ?> total</span>
</div>

<?php if (empty($messages)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-12 text-center">
        <p class="text-gray-500">No contact messages yet.</p>
    </div>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($messages as $msg): ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6" x-data="{ showReply: false }">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                        <?php
                        $statusColors = [
                            'unread' => 'bg-blue-50 text-blue-600',
                            'read' => 'bg-gray-100 text-gray-500',
                            'replied' => 'bg-green-50 text-green-600',
                        ];
                        $statusClass = $statusColors[$msg['status']] ?? 'bg-gray-100 text-gray-500';
                        ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?>"><?= ucfirst($msg['status']) ?></span>
                        <span class="text-sm text-gray-500"><?= formatDate($msg['created_at'], 'd M Y H:i') ?></span>
                    </div>
                    <p class="text-white font-medium"><?= h($msg['name']) ?> &lt;<?= h($msg['email']) ?>&gt;</p>
                    <?php if ($msg['subject']): ?>
                        <p class="text-sm text-gray-600 mt-1 font-medium"><?= h($msg['subject']) ?></p>
                    <?php endif; ?>
                    <p class="text-sm text-gray-500 mt-2 whitespace-pre-wrap"><?= h($msg['message']) ?></p>

                    <?php if ($msg['admin_reply']): ?>
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg border-l-2 border-green-500">
                        <p class="text-xs text-green-600 mb-1">Your reply (<?= formatDate($msg['replied_at'], 'd M Y H:i') ?>):</p>
                        <p class="text-sm text-gray-600 whitespace-pre-wrap"><?= h($msg['admin_reply']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="flex items-center space-x-2 ml-4">
                    <button @click="showReply = !showReply" class="text-indigo-600 hover:text-indigo-500 text-sm"><?= $msg['admin_reply'] ? 'Reply Again' : 'Reply' ?></button>
                    <form method="POST" action="/admin/contact-messages/slet" onsubmit="return confirm('Delete this message?')" class="inline">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                        <button type="submit" class="text-red-600 hover:text-red-300 text-sm">Delete</button>
                    </form>
                </div>
            </div>

            <!-- Reply form -->
            <form method="POST" action="/admin/contact-messages/reply" x-show="showReply" x-cloak class="mt-4 pt-4 border-t border-gray-200">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Reply to <?= h($msg['name']) ?></label>
                    <textarea name="reply" rows="4" required placeholder="Type your reply..."
                              class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="showReply = false" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-900">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Send Reply</button>
                </div>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
