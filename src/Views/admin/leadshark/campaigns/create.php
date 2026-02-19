<?php $pageTitle = 'Create Campaign'; $currentPage = 'leadshark-campaigns'; $tenant = currentTenant(); ob_start(); ?>

<div class="mb-6">
    <a href="/admin/leadshark/kampagner" class="inline-flex items-center text-sm text-gray-400 hover:text-white transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Campaigns
    </a>
</div>

<form method="POST" action="/admin/leadshark/kampagner/gem" x-data="campaignForm()" class="space-y-8">
    <?= csrfField() ?>

    <!-- Basic Information -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Campaign Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Campaign Name *</label>
                <input type="text" name="name" id="name" required
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="e.g., SaaS Decision Makers Q1 2025">
            </div>
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Describe the campaign goals and target audience..."></textarea>
            </div>
            <div>
                <label for="linkedin_account" class="block text-sm font-medium text-gray-300 mb-2">LinkedIn Account</label>
                <select name="linkedin_account_id" id="linkedin_account"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
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
                <p class="text-xs text-yellow-400 mt-1"><a href="/admin/leadshark/konto" class="underline">Connect a LinkedIn account</a> first.</p>
                <?php endif; ?>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                <select name="status" id="status"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="draft">Draft</option>
                    <option value="active">Active</option>
                    <option value="paused">Paused</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label for="linkedin_search_url" class="block text-sm font-medium text-gray-300 mb-2">LinkedIn Search URL</label>
                <input type="url" name="linkedin_search_url" id="linkedin_search_url"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="https://www.linkedin.com/search/results/people/?keywords=...">
                <p class="text-xs text-gray-500 mt-1">Paste the full LinkedIn search URL to define your target audience. Use LinkedIn Sales Navigator or standard search.</p>
            </div>
        </div>
    </div>

    <!-- Sequence Builder -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-white">Sequence Steps</h3>
                <p class="text-sm text-gray-400 mt-1">Define the automated sequence of actions for this campaign.</p>
            </div>
        </div>

        <div class="space-y-4">
            <template x-for="(step, index) in steps" :key="index">
                <div class="bg-gray-900 border border-gray-700 rounded-lg p-5 relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <span class="flex items-center justify-center w-7 h-7 rounded-full bg-indigo-600 text-white text-xs font-bold" x-text="index + 1"></span>
                            <span class="ml-2 text-sm font-medium text-gray-300">Step</span>
                        </div>
                        <button type="button" @click="removeStep(index)" class="text-red-400 hover:text-red-300 text-sm" x-show="steps.length > 1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Action Type</label>
                            <select :name="'steps[' + index + '][type]'" x-model="step.type"
                                class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="view_profile">View Profile</option>
                                <option value="connect">Send Connection Request</option>
                                <option value="message">Send Message</option>
                                <option value="follow_up">Follow-up Message</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Condition</label>
                            <select :name="'steps[' + index + '][condition]'" x-model="step.condition"
                                class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="always">Always</option>
                                <option value="if_accepted">If Connection Accepted</option>
                                <option value="if_no_reply">If No Reply</option>
                                <option value="if_replied">If Replied</option>
                            </select>
                        </div>
                        <div class="md:col-span-2" x-show="step.type === 'connect' || step.type === 'message' || step.type === 'follow_up'">
                            <label class="block text-sm font-medium text-gray-300 mb-2">Message Template</label>
                            <textarea :name="'steps[' + index + '][message_template]'" x-model="step.message_template" rows="3"
                                class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                                placeholder="Hi {{first_name}}, I noticed you work at {{company}}..."></textarea>
                            <p class="text-xs text-gray-500 mt-1">Available variables: <code class="text-indigo-400">{{first_name}}</code>, <code class="text-indigo-400">{{last_name}}</code>, <code class="text-indigo-400">{{company}}</code>, <code class="text-indigo-400">{{job_title}}</code></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Delay (Days)</label>
                            <input type="number" :name="'steps[' + index + '][delay_days]'" x-model="step.delay_days" min="0" max="90"
                                class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Delay (Hours)</label>
                            <input type="number" :name="'steps[' + index + '][delay_hours]'" x-model="step.delay_hours" min="0" max="23"
                                class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="0">
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <button type="button" @click="addStep()"
            class="mt-4 inline-flex items-center px-4 py-2 text-sm font-medium text-indigo-400 bg-indigo-900/30 border border-indigo-700/50 hover:bg-indigo-900/50 rounded-lg transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Step
        </button>
    </div>

    <!-- Submit -->
    <div class="flex items-center justify-end space-x-4">
        <a href="/admin/leadshark/kampagner" class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
            Cancel
        </a>
        <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
            Create Campaign
        </button>
    </div>
</form>

<script>
function campaignForm() {
    return {
        steps: [
            { type: 'view_profile', condition: 'always', message_template: '', delay_days: 0, delay_hours: 0 },
            { type: 'connect', condition: 'always', message_template: '', delay_days: 1, delay_hours: 0 },
        ],
        addStep() {
            this.steps.push({
                type: 'message',
                condition: 'if_accepted',
                message_template: '',
                delay_days: 2,
                delay_hours: 0,
            });
        },
        removeStep(index) {
            this.steps.splice(index, 1);
        }
    };
}
</script>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
