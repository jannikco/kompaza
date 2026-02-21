<?php $pageTitle = $newsletter ? 'Edit Newsletter' : 'Compose Newsletter'; $currentPage = 'newsletters'; $tenant = currentTenant(); ob_start(); ?>

<div class="mb-6">
    <a href="/admin/newsletters" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-900 transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Newsletters
    </a>
</div>

<!-- Save Draft Form -->
<form method="POST" action="/admin/newsletters/store" id="newsletterForm" class="space-y-6">
    <?= csrfField() ?>
    <?php if ($newsletter): ?>
        <input type="hidden" name="id" value="<?= $newsletter['id'] ?>">
    <?php endif; ?>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Newsletter Content</h3>
        <div class="space-y-6">
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                <input type="text" name="subject" id="subject" required
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    value="<?= h($newsletter['subject'] ?? '') ?>"
                    placeholder="Enter newsletter subject line...">
            </div>
            <div>
                <label for="body_html" class="block text-sm font-medium text-gray-700 mb-2">Body *</label>
                <textarea name="body_html" id="body_html" rows="12"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= h($newsletter['body_html'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                Save Draft
            </button>

            <?php if ($newsletter): ?>
            <button type="button" id="sendTestBtn" class="inline-flex items-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition border border-gray-300">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Send Test Email
            </button>

            <?php if ($newsletter['status'] === 'draft'): ?>
            <button type="button" id="sendAllBtn" class="inline-flex items-center px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Send to All Subscribers
            </button>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Hidden forms for test and send actions -->
<?php if ($newsletter): ?>
<form method="POST" action="/admin/newsletters/send-test" id="sendTestForm" class="hidden">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $newsletter['id'] ?>">
</form>

<form method="POST" action="/admin/newsletters/send" id="sendAllForm" class="hidden">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $newsletter['id'] ?>">
</form>
<?php endif; ?>

<!-- Confirm send modal -->
<?php if ($newsletter && $newsletter['status'] === 'draft'): ?>
<div id="confirmModal" class="fixed inset-0 bg-black/60 z-50 hidden items-center justify-center" x-data>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 max-w-md mx-4 w-full">
        <div class="flex items-center mb-4">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center mr-3">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Confirm Send</h3>
        </div>
        <p class="text-gray-600 text-sm mb-6">Are you sure you want to send this newsletter to all subscribers? This action cannot be undone.</p>
        <div class="flex items-center justify-end space-x-3">
            <button type="button" id="cancelSendBtn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                Cancel
            </button>
            <button type="button" id="confirmSendBtn" class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition">
                Yes, Send Now
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    tinymce.init({
        selector: '#body_html',
        height: 400,
        menubar: false,
        plugins: 'lists link',
        toolbar: 'undo redo | bold italic | bullist numlist | link',
        skin: 'oxide',
        content_css: 'default',
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; color: #e5e7eb; }',
    });

    // Send test email
    const sendTestBtn = document.getElementById('sendTestBtn');
    if (sendTestBtn) {
        sendTestBtn.addEventListener('click', function() {
            // Save draft first via TinyMCE, then submit test form
            tinymce.triggerSave();
            document.getElementById('sendTestForm').submit();
        });
    }

    // Send to all subscribers
    const sendAllBtn = document.getElementById('sendAllBtn');
    const confirmModal = document.getElementById('confirmModal');
    const cancelSendBtn = document.getElementById('cancelSendBtn');
    const confirmSendBtn = document.getElementById('confirmSendBtn');

    if (sendAllBtn && confirmModal) {
        sendAllBtn.addEventListener('click', function() {
            confirmModal.classList.remove('hidden');
            confirmModal.classList.add('flex');
        });

        cancelSendBtn.addEventListener('click', function() {
            confirmModal.classList.add('hidden');
            confirmModal.classList.remove('flex');
        });

        confirmSendBtn.addEventListener('click', function() {
            tinymce.triggerSave();
            document.getElementById('sendAllForm').submit();
        });

        // Close on background click
        confirmModal.addEventListener('click', function(e) {
            if (e.target === confirmModal) {
                confirmModal.classList.add('hidden');
                confirmModal.classList.remove('flex');
            }
        });
    }
</script>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
