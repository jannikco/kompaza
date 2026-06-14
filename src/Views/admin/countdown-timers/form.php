<?php
$isEdit = !empty($timer);
$pageTitle = $isEdit ? 'Edit Timer' : 'New Countdown Timer';
$currentPage = 'countdown-timers';
$tenant = currentTenant();
ob_start();
?>

<div class="flex items-center gap-3 mb-6">
    <a href="/admin/countdown-timers" class="text-gray-400 hover:text-gray-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <h2 class="text-2xl font-bold text-gray-900"><?= h($pageTitle) ?></h2>
</div>

<form method="POST" action="<?= $isEdit ? '/admin/countdown-timers/update' : '/admin/countdown-timers/store' ?>"
      x-data="{ timerType: '<?= h($timer['timer_type'] ?? 'fixed') ?>', expiredAction: '<?= h($timer['expired_action'] ?? 'hide') ?>', preset: '<?= h($timer['style_preset'] ?? 'default') ?>' }">
    <?= csrfField() ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$timer['id'] ?>">
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Settings -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Timer Settings</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Internal Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="<?= h($timer['name'] ?? '') ?>" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500" placeholder="e.g., Black Friday Sale">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Timer Type</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer transition"
                                   :class="timerType === 'fixed' ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-500' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="timer_type" value="fixed" x-model="timerType" class="sr-only">
                                <div>
                                    <span class="text-sm font-medium text-gray-900">Fixed Date</span>
                                    <p class="text-xs text-gray-500 mt-0.5">Counts down to a specific date/time</p>
                                </div>
                            </label>
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer transition"
                                   :class="timerType === 'evergreen' ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-500' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="timer_type" value="evergreen" x-model="timerType" class="sr-only">
                                <div>
                                    <span class="text-sm font-medium text-gray-900">Evergreen</span>
                                    <p class="text-xs text-gray-500 mt-0.5">Unique countdown per visitor</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div x-show="timerType === 'fixed'" x-transition>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date & Time</label>
                        <input type="datetime-local" name="end_date" value="<?= $isEdit && $timer['end_date'] ? date('Y-m-d\TH:i', strtotime($timer['end_date'])) : '' ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                    </div>
                    <div x-show="timerType === 'evergreen'" x-transition>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes)</label>
                        <input type="number" name="duration_minutes" value="<?= (int)($timer['duration_minutes'] ?? 60) ?>" min="1"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">Each visitor gets their own countdown starting when they first see the timer. Stored in a cookie.</p>
                    </div>
                </div>
            </div>

            <!-- Display Content -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Display Content</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Headline</label>
                        <input type="text" name="headline" value="<?= h($timer['headline'] ?? '') ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500" placeholder="e.g., Offer Ends In:">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subheadline</label>
                        <input type="text" name="subheadline" value="<?= h($timer['subheadline'] ?? '') ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500" placeholder="e.g., Don't miss this limited-time deal!">
                    </div>
                </div>
            </div>

            <!-- Expiration Behavior -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">When Timer Expires</h3>
                <div class="space-y-4">
                    <div>
                        <select name="expired_action" x-model="expiredAction" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                            <option value="hide">Hide the timer</option>
                            <option value="redirect">Redirect to URL</option>
                            <option value="show_message">Show a message</option>
                        </select>
                    </div>
                    <div x-show="expiredAction === 'redirect'" x-transition>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Redirect URL</label>
                        <input type="url" name="redirect_url" value="<?= h($timer['redirect_url'] ?? '') ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500" placeholder="https://...">
                    </div>
                    <div x-show="expiredAction === 'show_message'" x-transition>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expired Message</label>
                        <textarea name="expired_message" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500"><?= h($timer['expired_message'] ?? 'This offer has expired.') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Style & Preview -->
        <div class="space-y-6">
            <!-- Style -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Style</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Preset</label>
                        <select name="style_preset" x-model="preset" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                            <option value="default">Default (Dark)</option>
                            <option value="urgent">Urgent (Red)</option>
                            <option value="minimal">Minimal (Light)</option>
                            <option value="banner">Banner (Full Width)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Background Color</label>
                        <div class="flex gap-2">
                            <input type="color" name="bg_color" value="<?= h($timer['bg_color'] ?? '#111827') ?>" class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
                            <input type="text" value="<?= h($timer['bg_color'] ?? '#111827') ?>" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm bg-gray-50">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Text Color</label>
                        <div class="flex gap-2">
                            <input type="color" name="text_color" value="<?= h($timer['text_color'] ?? '#FFFFFF') ?>" class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
                            <input type="text" value="<?= h($timer['text_color'] ?? '#FFFFFF') ?>" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm bg-gray-50">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Accent Color</label>
                        <div class="flex gap-2">
                            <input type="color" name="accent_color" value="<?= h($timer['accent_color'] ?? '#EF4444') ?>" class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
                            <input type="text" value="<?= h($timer['accent_color'] ?? '#EF4444') ?>" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm bg-gray-50">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Status</h3>
                <select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-indigo-500">
                    <option value="active" <?= ($timer['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($timer['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <?php if ($isEdit): ?>
            <!-- Embed Code -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Embed Code</h3>
                <div class="bg-gray-900 rounded-lg p-3">
                    <code class="text-xs text-green-400 break-all">&lt;div data-countdown-timer-id="<?= (int)$timer['id'] ?>"&gt;&lt;/div&gt;</code>
                </div>
                <p class="text-xs text-gray-500 mt-2">Paste this code into any page where you want the timer to appear.</p>
            </div>
            <?php endif; ?>

            <button type="submit" class="w-full px-4 py-3 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                <?= $isEdit ? 'Update Timer' : 'Create Timer' ?>
            </button>
        </div>
    </div>
</form>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/admin/layouts/admin-layout.php'; ?>
