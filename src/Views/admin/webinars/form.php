<?php
$isEdit = !empty($webinar);
$pageTitle = $isEdit ? 'Edit Webinar' : 'Create Webinar';
$currentPage = 'webinars';
$bulletPoints = $isEdit && $webinar['bullet_points'] ? json_decode($webinar['bullet_points'], true) : [];
ob_start();
?>

<form method="POST" action="<?= $isEdit ? '/admin/webinars/update' : '/admin/webinars/store' ?>" enctype="multipart/form-data"
      x-data="webinarForm()" class="max-w-4xl">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= $webinar['id'] ?>">
    <?php endif; ?>

    <!-- Basic Info -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Webinar Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" value="<?= h($webinar['title'] ?? '') ?>" required
                       @input="if(!slugEdited) slug = $el.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" x-model="slug" @input="slugEdited = true" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Webinar Type</label>
                <select name="webinar_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="live" <?= ($webinar['webinar_type'] ?? 'live') === 'live' ? 'selected' : '' ?>>Live</option>
                    <option value="replay" <?= ($webinar['webinar_type'] ?? '') === 'replay' ? 'selected' : '' ?>>Replay</option>
                    <option value="evergreen" <?= ($webinar['webinar_type'] ?? '') === 'evergreen' ? 'selected' : '' ?>>Evergreen</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="draft" <?= ($webinar['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="registration_open" <?= ($webinar['status'] ?? '') === 'registration_open' ? 'selected' : '' ?>>Registration Open</option>
                    <option value="live" <?= ($webinar['status'] ?? '') === 'live' ? 'selected' : '' ?>>Live</option>
                    <option value="replay" <?= ($webinar['status'] ?? '') === 'replay' ? 'selected' : '' ?>>Replay</option>
                    <option value="archived" <?= ($webinar['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Scheduled Date & Time</label>
                <input type="datetime-local" name="scheduled_at" value="<?= $isEdit && $webinar['scheduled_at'] ? date('Y-m-d\TH:i', strtotime($webinar['scheduled_at'])) : '' ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes)</label>
                <input type="number" name="duration_minutes" value="<?= h($webinar['duration_minutes'] ?? 60) ?>" min="15"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"><?= h($webinar['description'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Host Info -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Host Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Host Name</label>
                <input type="text" name="host_name" value="<?= h($webinar['host_name'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Host Photo</label>
                <input type="file" name="host_image" accept="image/*"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <?php if ($isEdit && $webinar['host_image_path']): ?>
                <p class="text-xs text-gray-500 mt-1">Current: <?= h(basename($webinar['host_image_path'])) ?></p>
                <?php endif; ?>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Host Bio</label>
                <textarea name="host_bio" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"><?= h($webinar['host_bio'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Registration Page -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Registration Page</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Headline</label>
                <input type="text" name="registration_headline" value="<?= h($webinar['registration_headline'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Free Live Training: How to...">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Subheadline</label>
                <textarea name="registration_subheadline" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"><?= h($webinar['registration_subheadline'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CTA Button Text</label>
                <input type="text" name="registration_cta_text" value="<?= h($webinar['registration_cta_text'] ?? 'Register Now') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Registration Image</label>
                <input type="file" name="registration_image" accept="image/*"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">What You'll Learn (bullet points)</label>
                <template x-for="(bullet, index) in bullets" :key="index">
                    <div class="flex gap-2 mb-2">
                        <input type="text" :name="'bullet_points[' + index + ']'" x-model="bullets[index]"
                               class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg" placeholder="You will learn...">
                        <button type="button" @click="bullets.splice(index, 1)" class="text-red-500 hover:text-red-700 px-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
                <button type="button" @click="bullets.push('')" class="text-sm text-indigo-600 hover:text-indigo-700">+ Add bullet point</button>
            </div>
        </div>
    </div>

    <!-- Video / Embed -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Video & Embed</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Live/Main Embed URL</label>
                <input type="url" name="embed_url" value="<?= h($webinar['embed_url'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="YouTube/Zoom embed URL">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Replay URL</label>
                <input type="url" name="replay_url" value="<?= h($webinar['replay_url'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Video URL for replay">
            </div>
        </div>
    </div>

    <!-- Post-Webinar Offer -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Post-Webinar Offer</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Offer Product</label>
                <select name="offer_product_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">None</option>
                    <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($webinar['offer_product_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= h($p['name']) ?> (<?= formatMoney($p['price_dkk']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Offer Headline</label>
                <input type="text" name="offer_headline" value="<?= h($webinar['offer_headline'] ?? '') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Special offer for attendees...">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Offer Description</label>
                <textarea name="offer_description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"><?= h($webinar['offer_description'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Email Sequences -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Email Sequences</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reminder Sequence (pre-webinar)</label>
                <select name="reminder_sequence_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">None</option>
                    <?php foreach ($emailSequences as $es): ?>
                    <option value="<?= $es['id'] ?>" <?= ($webinar['reminder_sequence_id'] ?? '') == $es['id'] ? 'selected' : '' ?>><?= h($es['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Follow-up Sequence (post-webinar)</label>
                <select name="followup_sequence_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">None</option>
                    <?php foreach ($emailSequences as $es): ?>
                    <option value="<?= $es['id'] ?>" <?= ($webinar['followup_sequence_id'] ?? '') == $es['id'] ? 'selected' : '' ?>><?= h($es['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <?php if ($isEdit && !empty($registrations)): ?>
    <!-- Registrations -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Registrations (<?= count($registrations) ?>)</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Registered</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Attended</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach (array_slice($registrations, 0, 50) as $r): ?>
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900"><?= h($r['name']) ?></td>
                        <td class="px-4 py-2 text-sm text-gray-500"><?= h($r['email']) ?></td>
                        <td class="px-4 py-2 text-sm text-gray-500"><?= formatDate($r['registered_at']) ?></td>
                        <td class="px-4 py-2">
                            <?php if ($r['attended']): ?>
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Yes</span>
                            <?php else: ?>
                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">No</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <div class="flex items-center gap-3">
        <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
            <?= $isEdit ? 'Update Webinar' : 'Create Webinar' ?>
        </button>
        <a href="/admin/webinars" class="px-6 py-2.5 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition">Cancel</a>
    </div>
</form>

<script>
function webinarForm() {
    return {
        slug: '<?= h($webinar['slug'] ?? '') ?>',
        slugEdited: <?= $isEdit ? 'true' : 'false' ?>,
        bullets: <?= json_encode($bulletPoints ?: ['']) ?>,
    };
}
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
