<?php $pageTitle = 'Edit Post Automation'; $currentPage = 'connectpilot-automations'; $tenant = currentTenant(); ob_start(); ?>

<div class="mb-6">
    <a href="/admin/connectpilot/automations" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-900 transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Post Automations
    </a>
</div>

<!-- Stats Card -->
<div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-8">
    <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm text-center">
        <p class="text-2xl font-bold text-gray-900"><?= number_format($automation['comments_detected'] ?? 0) ?></p>
        <p class="text-xs text-gray-500 mt-1">Comments</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm text-center">
        <p class="text-2xl font-bold text-indigo-600"><?= number_format($automation['keyword_matches'] ?? 0) ?></p>
        <p class="text-xs text-gray-500 mt-1">Matches</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm text-center">
        <p class="text-2xl font-bold text-blue-600"><?= number_format($automation['replies_sent'] ?? 0) ?></p>
        <p class="text-xs text-gray-500 mt-1">Replies</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm text-center">
        <p class="text-2xl font-bold text-purple-600"><?= number_format($automation['dms_sent'] ?? 0) ?></p>
        <p class="text-xs text-gray-500 mt-1">DMs Sent</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm text-center">
        <p class="text-2xl font-bold text-green-600"><?= number_format($automation['leads_captured'] ?? 0) ?></p>
        <p class="text-xs text-gray-500 mt-1">Leads</p>
    </div>
</div>

<form method="POST" action="/admin/connectpilot/automations/update" x-data="automationForm()" class="space-y-8">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $automation['id'] ?>">

    <!-- Basic Information -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Basics</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Automation Name *</label>
                <input type="text" name="name" id="name" required
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    value="<?= h($automation['name']) ?>">
            </div>
            <div>
                <label for="linkedin_account_id" class="block text-sm font-medium text-gray-700 mb-2">LinkedIn Account *</label>
                <select name="linkedin_account_id" id="linkedin_account_id" required
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <?php foreach ($linkedinAccounts as $acc): ?>
                    <option value="<?= $acc['id'] ?>" <?= $acc['id'] == $automation['linkedin_account_id'] ? 'selected' : '' ?> <?= $acc['status'] !== 'active' ? 'disabled' : '' ?>>
                        <?= h($acc['linkedin_name'] ?: $acc['linkedin_email']) ?>
                        <?= $acc['status'] !== 'active' ? '(Disconnected)' : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" id="status"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="active" <?= $automation['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="paused" <?= $automation['status'] === 'paused' ? 'selected' : '' ?>>Paused</option>
                    <option value="completed" <?= $automation['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
        </div>
    </div>

    <!-- LinkedIn Post -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">LinkedIn Post</h3>
        <div>
            <label for="post_url" class="block text-sm font-medium text-gray-700 mb-2">Post URL *</label>
            <div class="flex space-x-3">
                <input type="url" name="post_url" id="post_url" required x-model="postUrl"
                    class="flex-1 px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    value="<?= h($automation['post_url']) ?>">
                <button type="button" @click="validatePost()" :disabled="validating"
                    class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 rounded-lg transition disabled:opacity-50">
                    <span x-show="!validating">Validate</span>
                    <span x-show="validating">Checking...</span>
                </button>
            </div>
            <?php if ($automation['post_urn']): ?>
            <p class="mt-2 text-sm text-green-600 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                URN: <span class="font-mono text-xs ml-1"><?= h($automation['post_urn']) ?></span>
            </p>
            <?php endif; ?>
            <div x-show="postValidated" x-cloak class="mt-2 text-sm text-green-600 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Post URL validated. URN: <span x-text="postUrn" class="ml-1 font-mono text-xs"></span>
            </div>
            <div x-show="postError" x-cloak class="mt-2 text-sm text-red-600" x-text="postError"></div>
        </div>
    </div>

    <!-- Trigger Keyword -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Trigger Keyword</h3>
        <div>
            <label for="trigger_keyword" class="block text-sm font-medium text-gray-700 mb-2">Keyword *</label>
            <input type="text" name="trigger_keyword" id="trigger_keyword" required x-model="keyword"
                class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                value="<?= h($automation['trigger_keyword']) ?>">
            <p class="text-xs text-gray-500 mt-2">Case-insensitive matching.</p>
        </div>
    </div>

    <!-- Auto-Reply -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Auto-Reply to Comment</h3>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="auto_reply_enabled" value="1" x-model="autoReplyEnabled" class="sr-only peer" <?= $automation['auto_reply_enabled'] ? 'checked' : '' ?>>
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
            </label>
        </div>
        <div x-show="autoReplyEnabled" x-collapse>
            <label for="auto_reply_template" class="block text-sm font-medium text-gray-700 mb-2">Reply Template</label>
            <textarea name="auto_reply_template" id="auto_reply_template" rows="3"
                class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= h($automation['auto_reply_template']) ?></textarea>
        </div>
    </div>

    <!-- Auto-DM -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Auto-DM</h3>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="auto_dm_enabled" value="1" x-model="autoDmEnabled" class="sr-only peer" <?= $automation['auto_dm_enabled'] ? 'checked' : '' ?>>
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
            </label>
        </div>
        <div x-show="autoDmEnabled" x-collapse>
            <label for="dm_template" class="block text-sm font-medium text-gray-700 mb-2">DM Template</label>
            <textarea name="dm_template" id="dm_template" rows="5" x-model="dmTemplate"
                class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"><?= h($automation['dm_template']) ?></textarea>
            <div class="flex flex-wrap gap-2 mt-2">
                <button type="button" @click="insertVariable('{{first_name}}')" class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-md hover:bg-indigo-100 transition">
                    {{first_name}}
                </button>
                <button type="button" @click="insertVariable('{{lead_magnet_url}}')" class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-md hover:bg-indigo-100 transition">
                    {{lead_magnet_url}}
                </button>
            </div>
            <div class="mt-4">
                <label for="lead_magnet_id" class="block text-sm font-medium text-gray-700 mb-2">Link Lead Magnet (optional)</label>
                <select name="lead_magnet_id" id="lead_magnet_id"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">None</option>
                    <?php foreach ($leadMagnets as $lm): ?>
                    <option value="<?= $lm['id'] ?>" <?= $lm['id'] == $automation['lead_magnet_id'] ? 'selected' : '' ?>><?= h($lm['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div class="flex items-center justify-between">
        <a href="/admin/connectpilot/automations/comments?id=<?= $automation['id'] ?>" class="text-sm text-indigo-600 hover:text-indigo-500">
            View Comments (<?= number_format($automation['comments_detected'] ?? 0) ?>)
        </a>
        <div class="flex items-center space-x-4">
            <a href="/admin/connectpilot/automations" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                Update Automation
            </button>
        </div>
    </div>
</form>

<script>
function automationForm() {
    return {
        postUrl: <?= json_encode($automation['post_url']) ?>,
        keyword: <?= json_encode($automation['trigger_keyword']) ?>,
        autoReplyEnabled: <?= $automation['auto_reply_enabled'] ? 'true' : 'false' ?>,
        autoDmEnabled: <?= $automation['auto_dm_enabled'] ? 'true' : 'false' ?>,
        dmTemplate: <?= json_encode($automation['dm_template'] ?? '') ?>,
        validating: false,
        postValidated: false,
        postUrn: '',
        postError: '',

        validatePost() {
            if (!this.postUrl) {
                this.postError = 'Please enter a post URL first.';
                return;
            }
            this.validating = true;
            this.postError = '';
            this.postValidated = false;

            fetch('/api/connectpilot/validate-post', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ post_url: this.postUrl })
            })
            .then(r => r.json())
            .then(data => {
                this.validating = false;
                if (data.success) {
                    this.postValidated = true;
                    this.postUrn = data.post_urn;
                } else {
                    this.postError = data.error || 'Validation failed.';
                }
            })
            .catch(() => {
                this.validating = false;
                this.postError = 'Network error. Please try again.';
            });
        },

        insertVariable(variable) {
            const textarea = document.getElementById('dm_template');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            this.dmTemplate = this.dmTemplate.substring(0, start) + variable + this.dmTemplate.substring(end);
            this.$nextTick(() => {
                textarea.focus();
                textarea.selectionStart = textarea.selectionEnd = start + variable.length;
            });
        }
    };
}
</script>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
