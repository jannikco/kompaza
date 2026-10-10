<?php
/** @var array $automation */
/** @var array $linkedinAccounts */
/** @var array $leadMagnets */
$a = $automation ?? [];
$isEdit = !empty($a['id']);
$kwCsv = $isEdit ? \App\Models\PostAutomation::keywordsCsv($a) : '';
?>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Basics</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Automation Name *</label>
                <input type="text" name="name" id="name" required x-model="name"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="e.g., Free PDF Guide Campaign"
                    value="<?= h($a['name'] ?? '') ?>">
            </div>
            <div>
                <label for="linkedin_account_id" class="block text-sm font-medium text-gray-700 mb-2">LinkedIn Account *</label>
                <select name="linkedin_account_id" id="linkedin_account_id" required
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <?php if (empty($linkedinAccounts)): ?>
                    <option value="">No accounts connected</option>
                    <?php else: ?>
                    <?php foreach ($linkedinAccounts as $acc): ?>
                    <option value="<?= $acc['id'] ?>" <?= (($a['linkedin_account_id'] ?? null) == $acc['id'] || (!$isEdit && $acc['status'] === 'active')) ? 'selected' : '' ?> <?= $acc['status'] !== 'active' ? 'disabled' : '' ?>>
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
                    <option value="draft" <?= ($a['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft (not running)</option>
                    <option value="active" <?= ($a['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="paused" <?= ($a['status'] ?? '') === 'paused' ? 'selected' : '' ?>>Paused</option>
                    <?php if ($isEdit): ?>
                    <option value="completed" <?= ($a['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <?php endif; ?>
                </select>
                <p class="text-xs text-gray-500 mt-1">New automations start as draft. Flip to Active when the templates look right.</p>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">LinkedIn Post</h3>
            <button type="button" @click="loadRecentPosts()" :disabled="loadingPosts"
                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 disabled:opacity-50">
                <span x-show="!loadingPosts">Load my recent posts</span>
                <span x-show="loadingPosts">Loading…</span>
            </button>
        </div>
        <div x-show="postsError" x-cloak class="mb-3 text-sm text-red-600" x-text="postsError"></div>
        <div x-show="recentPosts.length" x-cloak class="mb-4 divide-y divide-gray-100 border border-gray-200 rounded-lg overflow-hidden">
            <template x-for="post in recentPosts" :key="post.post_urn">
                <button type="button" @click="pickPost(post)"
                    class="w-full text-left px-4 py-3 hover:bg-indigo-50 transition">
                    <p class="text-sm text-gray-900 line-clamp-2" x-text="post.commentary || post.post_urn"></p>
                    <p class="text-xs text-gray-500 mt-1">
                        <span x-text="post.comments"></span> comments ·
                        <span x-text="post.reactions"></span> reactions ·
                        <span x-text="post.reposts"></span> reposts
                    </p>
                </button>
            </template>
        </div>
        <div>
            <label for="post_url" class="block text-sm font-medium text-gray-700 mb-2">Post URL *</label>
            <div class="flex space-x-3">
                <input type="url" name="post_url" id="post_url" required x-model="postUrl"
                    class="flex-1 px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="https://www.linkedin.com/feed/update/urn:li:activity:..."
                    value="<?= h($a['post_url'] ?? '') ?>">
                <button type="button" @click="validatePost()" :disabled="validating"
                    class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 rounded-lg transition disabled:opacity-50">
                    <span x-show="!validating">Validate</span>
                    <span x-show="validating">Checking...</span>
                </button>
            </div>
            <?php if (!empty($a['post_urn'])): ?>
            <p class="mt-2 text-sm text-green-600">URN: <span class="font-mono text-xs"><?= h($a['post_urn']) ?></span></p>
            <?php endif; ?>
            <div x-show="postValidated" x-cloak class="mt-2 text-sm text-green-600">
                Post URL validated. URN: <span x-text="postUrn" class="font-mono text-xs"></span>
            </div>
            <div x-show="postError" x-cloak class="mt-2 text-sm text-red-600" x-text="postError"></div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Trigger keywords</h3>
            <button type="button" @click="suggestSettings()" :disabled="suggesting"
                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 disabled:opacity-50">
                <span x-show="!suggesting">Suggest from post text</span>
                <span x-show="suggesting">Drafting…</span>
            </button>
        </div>
        <div x-show="suggestError" x-cloak class="mb-2 text-sm text-red-600" x-text="suggestError"></div>
        <label for="trigger_keywords" class="block text-sm font-medium text-gray-700 mb-2">Keywords * <span class="font-normal text-gray-500">(comma-separated, max 20)</span></label>
        <input type="text" name="trigger_keywords" id="trigger_keywords" required x-model="keywords"
            class="w-full px-4 py-2.5 bg-white border border-gray-300 text-gray-900 placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            placeholder="GUIDE, interested, send"
            value="<?= h($kwCsv) ?>">
        <p class="text-xs text-gray-500 mt-2">Case-insensitive substring match. A blank keyword is ignored so it cannot match every comment.</p>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Engagement</h3>
        <div class="space-y-4">
            <label class="flex items-start gap-3">
                <input type="checkbox" name="auto_like_enabled" value="1" class="mt-1 rounded border-gray-300 text-indigo-600" <?= !empty($a['auto_like_enabled']) ? 'checked' : '' ?>>
                <span>
                    <span class="block text-sm font-medium text-gray-900">Auto-like comments</span>
                    <span class="block text-xs text-gray-500">Like every new comment on the post (uses the reaction budget).</span>
                </span>
            </label>
            <label class="flex items-start gap-3">
                <input type="checkbox" name="auto_connect_enabled" value="1" class="mt-1 rounded border-gray-300 text-indigo-600" <?= !empty($a['auto_connect_enabled']) ? 'checked' : '' ?>>
                <span>
                    <span class="block text-sm font-medium text-gray-900">Auto-connect commenters</span>
                    <span class="block text-xs text-gray-500">Invite 2nd/3rd-degree commenters so the DM can land after they accept.</span>
                </span>
            </label>
            <label class="flex items-start gap-3">
                <input type="checkbox" name="partially_engage" value="1" class="mt-1 rounded border-gray-300 text-indigo-600" <?= !empty($a['partially_engage']) ? 'checked' : '' ?>>
                <span>
                    <span class="block text-sm font-medium text-gray-900">Skip comments you already replied to</span>
                    <span class="block text-xs text-gray-500">Avoids double-replying when you answered someone by hand.</span>
                </span>
            </label>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Auto-reply to comment</h3>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="auto_reply_enabled" value="1" x-model="autoReplyEnabled" class="sr-only peer" <?= ($a['auto_reply_enabled'] ?? 1) ? 'checked' : '' ?>>
                <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
            </label>
        </div>
        <div x-show="autoReplyEnabled" x-collapse class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Reply (1st-degree)</label>
                <textarea name="auto_reply_template" x-model="autoReplyTemplate" rows="3"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg"><?= h($a['auto_reply_template'] ?? 'Sent to your DMs! Check your inbox') ?></textarea>
                <p class="text-xs text-gray-500 mt-1">Use <code>{{fullNameMention}}</code> to @mention the commenter.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Reply (not yet connected)</label>
                <textarea name="non_first_degree_reply_template" x-model="nonFirstReply" rows="3"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg"><?= h($a['non_first_degree_reply_template'] ?? '') ?></textarea>
                <p class="text-xs text-gray-500 mt-1">Leave blank to reuse the 1st-degree reply. Typical: “Sending a connection request so I can DM you.”</p>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Auto-DM</h3>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="auto_dm_enabled" value="1" x-model="autoDmEnabled" class="sr-only peer" <?= ($a['auto_dm_enabled'] ?? 1) ? 'checked' : '' ?>>
                <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
            </label>
        </div>
        <div x-show="autoDmEnabled" x-collapse>
            <label class="block text-sm font-medium text-gray-700 mb-2">DM template</label>
            <textarea name="dm_template" id="dm_template" rows="5" x-model="dmTemplate"
                class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg font-mono text-sm"><?= h($a['dm_template'] ?? '') ?></textarea>
            <div class="flex flex-wrap gap-2 mt-2">
                <button type="button" @click="insertVariable('{{firstName}}')" class="px-2.5 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-md">{{firstName}}</button>
                <button type="button" @click="insertVariable('{{lead_magnet_url}}')" class="px-2.5 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-md">{{lead_magnet_url}}</button>
            </div>
            <div class="mt-4">
                <label for="lead_magnet_id" class="block text-sm font-medium text-gray-700 mb-2">Link lead magnet (optional)</label>
                <select name="lead_magnet_id" id="lead_magnet_id"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg">
                    <option value="">None</option>
                    <?php foreach ($leadMagnets as $lm): ?>
                    <option value="<?= $lm['id'] ?>" <?= ($a['lead_magnet_id'] ?? '') == $lm['id'] ? 'selected' : '' ?>><?= h($lm['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Follow-up DM</h3>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="follow_up_enabled" value="1" x-model="followUpEnabled" class="sr-only peer" <?= !empty($a['follow_up_enabled']) ? 'checked' : '' ?>>
                <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
            </label>
        </div>
        <div x-show="followUpEnabled" x-collapse class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Follow-up template</label>
                <textarea name="follow_up_template" x-model="followUpTemplate" rows="4"
                    class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg font-mono text-sm"><?= h($a['follow_up_template'] ?? '') ?></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Delay (minutes)</label>
                    <input type="number" name="follow_up_delay_minutes" min="1" max="10080"
                        value="<?= (int) ($a['follow_up_delay_minutes'] ?? 120) ?>"
                        class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg">
                    <p class="text-xs text-gray-500 mt-1">Default 120 (2 hours). Max 7 days.</p>
                </div>
                <label class="flex items-center gap-2 mt-8">
                    <input type="checkbox" name="follow_up_only_if_no_response" value="1" class="rounded border-gray-300 text-indigo-600" <?= ($a['follow_up_only_if_no_response'] ?? 1) ? 'checked' : '' ?>>
                    <span class="text-sm text-gray-700">Only if they have not replied</span>
                </label>
            </div>
        </div>
    </div>
