<?php $pageTitle = 'Edit Post Automation'; $currentPage = 'connectpilot-automations'; $tenant = currentTenant(); ob_start(); ?>

<div class="mb-6">
    <a href="/admin/connectpilot/automations" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-900 transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Post Automations
    </a>
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mb-8">
    <?php
    $tiles = [
        ['Comments', $automation['comments_detected'] ?? 0, 'text-gray-900'],
        ['Matches', $automation['keyword_matches'] ?? 0, 'text-indigo-600'],
        ['Replies', $automation['replies_sent'] ?? 0, 'text-blue-600'],
        ['Likes', $automation['likes_sent'] ?? 0, 'text-amber-600'],
        ['Connects', $automation['connections_sent'] ?? 0, 'text-sky-600'],
        ['DMs', $automation['dms_sent'] ?? 0, 'text-purple-600'],
        ['Follow-ups', $automation['follow_ups_sent'] ?? 0, 'text-pink-600'],
        ['Leads', $automation['leads_captured'] ?? 0, 'text-green-600'],
    ];
    foreach ($tiles as [$label, $n, $color]):
    ?>
    <div class="bg-white rounded-xl p-3 border border-gray-200 shadow-sm text-center">
        <p class="text-xl font-bold <?= $color ?>"><?= number_format((int) $n) ?></p>
        <p class="text-xs text-gray-500 mt-1"><?= $label ?></p>
    </div>
    <?php endforeach; ?>
</div>

<form method="POST" action="/admin/connectpilot/automations/update"
    x-data="automationForm({
        name: <?= json_encode($automation['name']) ?>,
        postUrl: <?= json_encode($automation['post_url']) ?>,
        keywords: <?= json_encode(\App\Models\PostAutomation::keywordsCsv($automation)) ?>,
        autoReplyEnabled: <?= !empty($automation['auto_reply_enabled']) ? 'true' : 'false' ?>,
        autoReplyTemplate: <?= json_encode($automation['auto_reply_template'] ?? '') ?>,
        nonFirstReply: <?= json_encode($automation['non_first_degree_reply_template'] ?? '') ?>,
        autoDmEnabled: <?= !empty($automation['auto_dm_enabled']) ? 'true' : 'false' ?>,
        dmTemplate: <?= json_encode($automation['dm_template'] ?? '') ?>,
        followUpEnabled: <?= !empty($automation['follow_up_enabled']) ? 'true' : 'false' ?>,
        followUpTemplate: <?= json_encode($automation['follow_up_template'] ?? '') ?>
    })" class="space-y-8">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $automation['id'] ?>">
    <?php include __DIR__ . '/_fields.php'; ?>

    <div class="flex items-center justify-between">
        <a href="/admin/connectpilot/automations/comments?id=<?= $automation['id'] ?>" class="text-sm text-indigo-600 hover:text-indigo-500">
            View comments (<?= number_format($automation['comments_detected'] ?? 0) ?>)
        </a>
        <div class="flex items-center space-x-4">
            <a href="/admin/connectpilot/automations" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">Cancel</a>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">Update automation</button>
        </div>
    </div>
</form>

<?php include __DIR__ . '/_form-js.php'; ?>
<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
