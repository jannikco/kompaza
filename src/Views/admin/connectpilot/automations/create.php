<?php $pageTitle = 'Create Post Automation'; $currentPage = 'connectpilot-automations'; $tenant = currentTenant(); $automation = []; ob_start(); ?>

<div class="mb-6">
    <a href="/admin/connectpilot/automations" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-900 transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Post Automations
    </a>
</div>

<form method="POST" action="/admin/connectpilot/automations/store" x-data="automationForm({})" class="space-y-8">
    <?= csrfField() ?>
    <?php include __DIR__ . '/_fields.php'; ?>

    <div class="flex items-center justify-end space-x-4">
        <a href="/admin/connectpilot/automations" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Cancel</a>
        <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
            Save automation
        </button>
    </div>
</form>

<?php include __DIR__ . '/_form-js.php'; ?>
<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
