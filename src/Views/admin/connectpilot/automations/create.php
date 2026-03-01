<?php $pageTitle = 'Create Post Automation'; $currentPage = 'connectpilot-automations'; $tenant = currentTenant(); ob_start(); ?>

<div class="mb-6">
    <a href="/admin/connectpilot/automations" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-900 transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Post Automations
    </a>
</div>

<form method="POST" action="/admin/connectpilot/automations/store" x-data="automationForm()" class="space-y-8">
    <?= csrfField() ?>

    <!-- Basic Information -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Basics</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Automation Name *</label>
                <input type="text" name="name" id="name" required
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="e.g., Free PDF Guide Campaign">
            </div>
            <div>
                <label for="linkedin_account_id" class="block text-sm font-medium text-gray-700 mb-2">LinkedIn Account *</label>
                <select name="linkedin_account_id" id="linkedin_account_id" required
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <?php if (empty($linkedinAccounts)): ?>
                    <option value="">No accounts connected</option>
                    <?php else: ?>
                    <?php foreach ($linkedinAccounts as $acc): ?>
                    <option value="<?= $acc['id'] ?>" <?= $acc['status'] !== 'active' ? 'disabled' : '' ?>>
                        <?= h($acc['linkedin_name'] ?: $acc['linkedin_email']) ?>
                        <?= $acc['status'] !== 'active' ? '(Disconnected)' : '' ?>
                    </option>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if (empty($linkedinAccounts)): ?>
                <p class="text-xs text-yellow-600 mt-1"><a href="/admin/connectpilot/konto" class="underline">Connect a LinkedIn account</a> first.</p>
                <?php endif; ?>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" id="status"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="active">Active</option>
                    <option value="paused">Paused</option>
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
                    placeholder="https://www.linkedin.com/feed/update/urn:li:activity:...">
                <button type="button" @click="validatePost()" :disabled="validating"
                    class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 rounded-lg transition disabled:opacity-50">
                    <span x-show="!validating">Validate</span>
                    <span x-show="validating">Checking...</span>
                </button>
            </div>
            <div x-show="postValidated" x-cloak class="mt-2 text-sm text-green-600 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Post URL validated. URN: <span x-text="postUrn" class="ml-1 font-mono text-xs"></span>
            </div>
            <div x-show="postError" x-cloak class="mt-2 text-sm text-red-600" x-text="postError"></div>
            <p class="text-xs text-gray-500 mt-2">Paste the full URL of your LinkedIn post. Supported formats: feed/update, posts/ URLs.</p>
        </div>
    </div>

    <!-- Trigger Keyword -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Trigger Keyword</h3>
        <div>
            <label for="trigger_keyword" class="block text-sm font-medium text-gray-700 mb-2">Keyword *</label>
            <input type="text" name="trigger_keyword" id="trigger_keyword" required x-model="keyword"
                class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="e.g., GUIDE">
            <p class="text-xs text-gray-500 mt-2">When someone comments with this keyword, the automation triggers. Matching is case-insensitive.</p>
        </div>
    </div>

    <!-- Auto-Reply -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Auto-Reply to Comment</h3>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="auto_reply_enabled" value="1" x-model="autoReplyEnabled" class="sr-only peer" checked>
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
            </label>
        </div>
        <div x-show="autoReplyEnabled" x-collapse>
            <label for="auto_reply_template" class="block text-sm font-medium text-gray-700 mb-2">Reply Template</label>
            <textarea name="auto_reply_template" id="auto_reply_template" rows="3"
                class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="Sent to your DMs! Check your inbox">Sent to your DMs! Check your inbox</textarea>
            <p class="text-xs text-gray-500 mt-2">This text will be posted as a reply to the commenter's comment.</p>
            <div x-show="keyword" class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                <p class="text-xs font-medium text-gray-500 mb-1">Preview:</p>
                <p class="text-sm text-gray-700">When someone comments "<span class="font-semibold text-indigo-600" x-text="keyword"></span>", you'll auto-reply with the template above.</p>
            </div>
        </div>
    </div>

    <!-- Auto-DM -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Auto-DM</h3>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="auto_dm_enabled" value="1" x-model="autoDmEnabled" class="sr-only peer" checked>
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
            </label>
        </div>
        <div x-show="autoDmEnabled" x-collapse>
            <label for="dm_template" class="block text-sm font-medium text-gray-700 mb-2">DM Template</label>
            <textarea name="dm_template" id="dm_template" rows="5" x-model="dmTemplate"
                class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                placeholder="Hi {{first_name}}, thanks for your interest! Here's the resource: {{lead_magnet_url}}"></textarea>
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
                <select name="lead_magnet_id" id="lead_magnet_id" x-model="leadMagnetId"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">None</option>
                    <?php foreach ($leadMagnets as $lm): ?>
                    <option value="<?= $lm['id'] ?>" data-slug="<?= h($lm['slug']) ?>"><?= h($lm['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-500 mt-1">When linked, <code class="text-indigo-600">{{lead_magnet_url}}</code> will resolve to the lead magnet's landing page URL in the DM.</p>
            </div>
        </div>
    </div>

    <!-- Flow Preview -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Automation Flow</h3>
        <div class="flex items-start space-x-4">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-sm font-bold">1</div>
                <div class="w-0.5 h-8 bg-gray-200 my-1"></div>
                <div class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-sm font-bold">2</div>
                <div class="w-0.5 h-8 bg-gray-200 my-1"></div>
                <div class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-sm font-bold">3</div>
                <div class="w-0.5 h-8 bg-gray-200 my-1"></div>
                <div class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm font-bold">4</div>
            </div>
            <div class="flex-1 space-y-6 pt-1">
                <div>
                    <p class="text-sm font-medium text-gray-900">Someone comments on your post</p>
                    <p class="text-xs text-gray-500">Cron checks for new comments every 5 minutes</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Keyword "<span x-text="keyword || '...'" class="text-indigo-600"></span>" detected</p>
                    <p class="text-xs text-gray-500">Case-insensitive matching</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900" x-show="autoReplyEnabled">Auto-reply posted to comment thread</p>
                    <p class="text-sm font-medium text-gray-400" x-show="!autoReplyEnabled">Auto-reply disabled</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900" x-show="autoDmEnabled">DM sent with resource link + lead created</p>
                    <p class="text-sm font-medium text-gray-400" x-show="!autoDmEnabled">Auto-DM disabled</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div class="flex items-center justify-end space-x-4">
        <a href="/admin/connectpilot/automations" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
            Create Automation
        </button>
    </div>
</form>

<script>
function automationForm() {
    return {
        postUrl: '',
        keyword: '',
        autoReplyEnabled: true,
        autoDmEnabled: true,
        dmTemplate: 'Hi {{first_name}}, thanks for your interest! Here\'s the resource: {{lead_magnet_url}}',
        leadMagnetId: '',
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
