<?php
$pageTitle = 'Book a Consultation';
$tenant = currentTenant();
$metaDescription = 'Book a consultation with us';
ob_start();
?>

<section class="py-16 lg:py-24">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900">Book a Consultation</h1>
            <p class="mt-3 text-gray-600 max-w-xl mx-auto">Choose a consultation type, tell us about your project, and we will get back to you with a confirmed time.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                    <?php foreach ($errors as $error): ?>
                        <li><?= h($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="/consultation/submit" method="POST" class="space-y-8">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">

            <!-- Consultation Type Cards -->
            <?php if (!empty($types)): ?>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-3">Select Consultation Type <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-data="{ selected: '<?= h($old['type_id'] ?? '') ?>' }">
                    <?php foreach ($types as $type): ?>
                    <label class="relative cursor-pointer" @click="selected = '<?= $type['id'] ?>'">
                        <input type="radio" name="type_id" value="<?= $type['id'] ?>" class="sr-only peer"
                               <?= ($old['type_id'] ?? '') == $type['id'] ? 'checked' : '' ?>
                               :checked="selected === '<?= $type['id'] ?>'">
                        <div class="bg-white rounded-xl border-2 p-5 transition-all"
                             :class="selected === '<?= $type['id'] ?>' ? 'border-brand ring-2 ring-brand/20 bg-brand/5' : 'border-gray-200 hover:border-gray-300'">
                            <div class="flex items-start justify-between mb-2">
                                <h3 class="text-base font-semibold text-gray-900"><?= h($type['name']) ?></h3>
                                <div class="flex items-center justify-center w-5 h-5 rounded-full border-2 flex-shrink-0 ml-3 transition-colors"
                                     :class="selected === '<?= $type['id'] ?>' ? 'border-brand bg-brand' : 'border-gray-300'">
                                    <svg x-show="selected === '<?= $type['id'] ?>'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </div>
                            </div>
                            <?php if ($type['description']): ?>
                                <p class="text-sm text-gray-500 mb-3"><?= h($type['description']) ?></p>
                            <?php endif; ?>
                            <div class="flex items-center gap-4 text-sm">
                                <span class="inline-flex items-center text-gray-600">
                                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <?= (int)$type['duration_minutes'] ?> min
                                </span>
                                <?php if ((float)$type['price_dkk'] > 0): ?>
                                    <span class="font-semibold text-gray-900"><?= formatMoney($type['price_dkk']) ?></span>
                                <?php else: ?>
                                    <span class="font-semibold text-green-600">Free</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Customer Information -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Your Information</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" id="customer_name" name="customer_name" required
                               value="<?= h($old['customer_name'] ?? '') ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                               placeholder="Your full name">
                    </div>
                    <div>
                        <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" id="customer_email" name="customer_email" required
                               value="<?= h($old['customer_email'] ?? '') ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                               placeholder="you@example.com">
                    </div>
                    <div>
                        <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="tel" id="customer_phone" name="customer_phone"
                               value="<?= h($old['customer_phone'] ?? '') ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                               placeholder="+45 12 34 56 78">
                    </div>
                    <div>
                        <label for="company" class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                        <input type="text" id="company" name="company"
                               value="<?= h($old['company'] ?? '') ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                               placeholder="Your company name">
                    </div>
                </div>
            </div>

            <!-- Project Details -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Project Details</h2>
                <div class="space-y-5">
                    <div>
                        <label for="project_description" class="block text-sm font-medium text-gray-700 mb-1">Project Description</label>
                        <textarea id="project_description" name="project_description" rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                                  placeholder="Tell us briefly about your project or what you need help with..."><?= h($old['project_description'] ?? '') ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="preferred_date" class="block text-sm font-medium text-gray-700 mb-1">Preferred Date <span class="text-red-500">*</span></label>
                            <input type="date" id="preferred_date" name="preferred_date" required
                                   value="<?= h($old['preferred_date'] ?? '') ?>"
                                   min="<?= date('Y-m-d') ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent">
                        </div>
                        <div>
                            <label for="preferred_time" class="block text-sm font-medium text-gray-700 mb-1">Preferred Time</label>
                            <select id="preferred_time" name="preferred_time"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent">
                                <option value="morning" <?= ($old['preferred_time'] ?? '') === 'morning' ? 'selected' : '' ?>>Morning (9:00 - 12:00)</option>
                                <option value="afternoon" <?= ($old['preferred_time'] ?? '') === 'afternoon' ? 'selected' : '' ?>>Afternoon (12:00 - 17:00)</option>
                                <option value="evening" <?= ($old['preferred_time'] ?? '') === 'evening' ? 'selected' : '' ?>>Evening (17:00 - 20:00)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Urgency Selector -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">How urgent is this?</label>
                        <div class="grid grid-cols-3 gap-3" x-data="{ urgency: '<?= h($old['urgency'] ?? 'medium') ?>' }">
                            <label class="relative cursor-pointer" @click="urgency = 'low'">
                                <input type="radio" name="urgency" value="low" class="sr-only"
                                       <?= ($old['urgency'] ?? 'medium') === 'low' ? 'checked' : '' ?>
                                       :checked="urgency === 'low'">
                                <div class="text-center rounded-lg border-2 py-3 px-4 transition-all"
                                     :class="urgency === 'low' ? 'border-gray-500 bg-gray-50 ring-2 ring-gray-200' : 'border-gray-200 hover:border-gray-300'">
                                    <div class="text-sm font-medium text-gray-700">Low</div>
                                    <div class="text-xs text-gray-500 mt-0.5">No rush</div>
                                </div>
                            </label>
                            <label class="relative cursor-pointer" @click="urgency = 'medium'">
                                <input type="radio" name="urgency" value="medium" class="sr-only"
                                       <?= ($old['urgency'] ?? 'medium') === 'medium' ? 'checked' : '' ?>
                                       :checked="urgency === 'medium'">
                                <div class="text-center rounded-lg border-2 py-3 px-4 transition-all"
                                     :class="urgency === 'medium' ? 'border-yellow-500 bg-yellow-50 ring-2 ring-yellow-200' : 'border-gray-200 hover:border-gray-300'">
                                    <div class="text-sm font-medium text-gray-700">Medium</div>
                                    <div class="text-xs text-gray-500 mt-0.5">Within a week</div>
                                </div>
                            </label>
                            <label class="relative cursor-pointer" @click="urgency = 'high'">
                                <input type="radio" name="urgency" value="high" class="sr-only"
                                       <?= ($old['urgency'] ?? 'medium') === 'high' ? 'checked' : '' ?>
                                       :checked="urgency === 'high'">
                                <div class="text-center rounded-lg border-2 py-3 px-4 transition-all"
                                     :class="urgency === 'high' ? 'border-red-500 bg-red-50 ring-2 ring-red-200' : 'border-gray-200 hover:border-gray-300'">
                                    <div class="text-sm font-medium text-gray-700">High</div>
                                    <div class="text-xs text-gray-500 mt-0.5">ASAP</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit"
                    class="w-full btn-brand px-6 py-4 text-white font-semibold rounded-xl transition text-base shadow-sm hover:shadow-md">
                Book Consultation
            </button>
        </form>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
