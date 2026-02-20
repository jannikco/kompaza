<?php
$pageTitle = 'Settings';
$currentPage = 'settings';
$tenant = currentTenant();
ob_start();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-white">Settings</h2>
    <p class="text-sm text-gray-400 mt-1">Configure your site branding, contact information, integrations, and advanced options.</p>
</div>

<form method="POST" action="/admin/indstillinger/opdater" enctype="multipart/form-data" class="space-y-8">
    <?= csrfField() ?>

    <!-- Branding -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
            Branding
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="company_name" class="block text-sm font-medium text-gray-300 mb-2">Company Name</label>
                <input type="text" name="company_name" id="company_name"
                    value="<?= h($settings['company_name'] ?? $tenant['company_name'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Your Company Name">
            </div>
            <div>
                <label for="tagline" class="block text-sm font-medium text-gray-300 mb-2">Tagline</label>
                <input type="text" name="tagline" id="tagline"
                    value="<?= h($settings['tagline'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Your company tagline">
            </div>
            <div>
                <label for="logo" class="block text-sm font-medium text-gray-300 mb-2">Logo</label>
                <?php if (!empty($settings['logo'])): ?>
                    <div class="mb-3 flex items-center space-x-3">
                        <img src="<?= h($settings['logo']) ?>" alt="Current logo" class="h-10 w-auto rounded border border-gray-600">
                        <span class="text-sm text-gray-400">Current logo</span>
                    </div>
                <?php endif; ?>
                <input type="file" name="logo" id="logo" accept="image/*"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
            </div>
            <div class="space-y-4">
                <div>
                    <label for="primary_color" class="block text-sm font-medium text-gray-300 mb-2">Primary Color</label>
                    <div class="flex items-center space-x-3">
                        <input type="color" name="primary_color" id="primary_color"
                            value="<?= h($settings['primary_color'] ?? '#4f46e5') ?>"
                            class="w-12 h-10 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer">
                        <input type="text" id="primary_color_text"
                            value="<?= h($settings['primary_color'] ?? '#4f46e5') ?>"
                            class="flex-1 px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            oninput="document.getElementById('primary_color').value = this.value"
                            onchange="document.getElementById('primary_color').value = this.value">
                    </div>
                </div>
                <div>
                    <label for="secondary_color" class="block text-sm font-medium text-gray-300 mb-2">Secondary Color</label>
                    <div class="flex items-center space-x-3">
                        <input type="color" name="secondary_color" id="secondary_color"
                            value="<?= h($settings['secondary_color'] ?? '#0ea5e9') ?>"
                            class="w-12 h-10 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer">
                        <input type="text" id="secondary_color_text"
                            value="<?= h($settings['secondary_color'] ?? '#0ea5e9') ?>"
                            class="flex-1 px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            oninput="document.getElementById('secondary_color').value = this.value"
                            onchange="document.getElementById('secondary_color').value = this.value">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Contact Information
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="contact_email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                <input type="email" name="contact_email" id="contact_email"
                    value="<?= h($settings['contact_email'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="info@example.com">
            </div>
            <div>
                <label for="contact_phone" class="block text-sm font-medium text-gray-300 mb-2">Phone</label>
                <input type="text" name="contact_phone" id="contact_phone"
                    value="<?= h($settings['contact_phone'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="+45 12 34 56 78">
            </div>
            <div>
                <label for="contact_address" class="block text-sm font-medium text-gray-300 mb-2">Address</label>
                <input type="text" name="contact_address" id="contact_address"
                    value="<?= h($settings['contact_address'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Street, City, Postal Code">
            </div>
            <div>
                <label for="cvr_number" class="block text-sm font-medium text-gray-300 mb-2">CVR Number</label>
                <input type="text" name="cvr_number" id="cvr_number"
                    value="<?= h($settings['cvr_number'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="DK12345678">
            </div>
        </div>
    </div>

    <!-- Integrations -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6" x-data="{ emailService: '<?= h($tenant['email_service'] ?? 'kompaza') ?>' }">
        <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            Integrations
        </h3>

        <!-- Email Service Provider -->
        <div class="mb-6">
            <label for="email_service" class="block text-sm font-medium text-gray-300 mb-2">Email Service Provider</label>
            <select name="email_service" id="email_service" x-model="emailService"
                class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="kompaza">Kompaza (Platform Default)</option>
                <option value="brevo">Brevo (Own Account)</option>
                <option value="mailgun">Mailgun</option>
                <option value="smtp">Own SMTP Server</option>
            </select>
        </div>

        <!-- Kompaza (default) -->
        <div x-show="emailService === 'kompaza'" x-cloak class="mb-6">
            <p class="text-sm text-gray-400">No configuration needed. Emails are sent via the Kompaza platform.</p>
        </div>

        <!-- Brevo fields -->
        <div x-show="emailService === 'brevo'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="brevo_api_key" class="block text-sm font-medium text-gray-300 mb-2">Brevo API Key</label>
                <input type="password" name="brevo_api_key" id="brevo_api_key"
                    value="<?= h($tenant['brevo_api_key'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="xkeysib-...">
            </div>
            <div>
                <label for="brevo_list_id" class="block text-sm font-medium text-gray-300 mb-2">Brevo List ID</label>
                <input type="text" name="brevo_list_id" id="brevo_list_id"
                    value="<?= h($tenant['brevo_list_id'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="e.g., 3">
            </div>
        </div>

        <!-- Mailgun fields -->
        <div x-show="emailService === 'mailgun'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="mailgun_api_key" class="block text-sm font-medium text-gray-300 mb-2">Mailgun API Key</label>
                <input type="password" name="mailgun_api_key" id="mailgun_api_key"
                    value="<?= h($tenant['mailgun_api_key'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="key-...">
            </div>
            <div>
                <label for="mailgun_domain" class="block text-sm font-medium text-gray-300 mb-2">Mailgun Domain</label>
                <input type="text" name="mailgun_domain" id="mailgun_domain"
                    value="<?= h($tenant['mailgun_domain'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="mg.example.com">
            </div>
            <div class="md:col-span-2">
                <p class="text-sm text-yellow-400">Contact list sync is only available with Brevo. Newsletter signups will be recorded locally but not synced to an external list.</p>
            </div>
        </div>

        <!-- SMTP fields -->
        <div x-show="emailService === 'smtp'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="smtp_host" class="block text-sm font-medium text-gray-300 mb-2">SMTP Host</label>
                <input type="text" name="smtp_host" id="smtp_host"
                    value="<?= h($tenant['smtp_host'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="smtp.example.com">
            </div>
            <div>
                <label for="smtp_port" class="block text-sm font-medium text-gray-300 mb-2">SMTP Port</label>
                <input type="number" name="smtp_port" id="smtp_port"
                    value="<?= h($tenant['smtp_port'] ?? '587') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="587">
            </div>
            <div>
                <label for="smtp_username" class="block text-sm font-medium text-gray-300 mb-2">SMTP Username</label>
                <input type="text" name="smtp_username" id="smtp_username"
                    value="<?= h($tenant['smtp_username'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="user@example.com">
            </div>
            <div>
                <label for="smtp_password" class="block text-sm font-medium text-gray-300 mb-2">SMTP Password</label>
                <input type="password" name="smtp_password" id="smtp_password"
                    value="<?= h($tenant['smtp_password'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="Password">
            </div>
            <div>
                <label for="smtp_encryption" class="block text-sm font-medium text-gray-300 mb-2">Encryption</label>
                <select name="smtp_encryption" id="smtp_encryption"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="tls" <?= ($tenant['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (Recommended)</option>
                    <option value="ssl" <?= ($tenant['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                    <option value="none" <?= ($tenant['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <p class="text-sm text-yellow-400">Contact list sync is only available with Brevo. Newsletter signups will be recorded locally but not synced to an external list.</p>
            </div>
        </div>

        <!-- Stripe & GA (always visible) -->
        <div class="border-t border-gray-700 pt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="stripe_publishable_key" class="block text-sm font-medium text-gray-300 mb-2">Stripe Publishable Key</label>
                <input type="text" name="stripe_publishable_key" id="stripe_publishable_key"
                    value="<?= h($settings['stripe_publishable_key'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="pk_live_...">
            </div>
            <div>
                <label for="stripe_secret_key" class="block text-sm font-medium text-gray-300 mb-2">Stripe Secret Key</label>
                <input type="password" name="stripe_secret_key" id="stripe_secret_key"
                    value="<?= h($settings['stripe_secret_key'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="sk_live_...">
            </div>
            <div class="md:col-span-2">
                <label for="google_analytics_id" class="block text-sm font-medium text-gray-300 mb-2">Google Analytics ID</label>
                <input type="text" name="google_analytics_id" id="google_analytics_id"
                    value="<?= h($settings['google_analytics_id'] ?? '') ?>"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    placeholder="G-XXXXXXXXXX">
            </div>
        </div>
    </div>

    <!-- Advanced -->
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Advanced
        </h3>
        <div class="space-y-6">
            <div>
                <label for="custom_css" class="block text-sm font-medium text-gray-300 mb-2">Custom CSS</label>
                <textarea name="custom_css" id="custom_css" rows="6"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                    placeholder="/* Add custom CSS styles here */"><?= h($settings['custom_css'] ?? '') ?></textarea>
                <p class="text-xs text-gray-500 mt-2">Custom CSS that will be injected into your public-facing pages.</p>
            </div>
            <div>
                <label for="custom_footer_html" class="block text-sm font-medium text-gray-300 mb-2">Custom Footer HTML</label>
                <textarea name="custom_footer_html" id="custom_footer_html" rows="6"
                    class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 text-white placeholder-gray-400 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                    placeholder="<!-- Custom HTML for your footer -->"><?= h($settings['custom_footer_html'] ?? '') ?></textarea>
                <p class="text-xs text-gray-500 mt-2">Custom HTML that will be rendered in the footer of your public site. Useful for tracking scripts, chat widgets, etc.</p>
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div class="flex items-center justify-end space-x-4">
        <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
            Save Settings
        </button>
    </div>
</form>

<script>
    // Sync color pickers with text inputs
    document.getElementById('primary_color').addEventListener('input', function() {
        document.getElementById('primary_color_text').value = this.value;
    });
    document.getElementById('secondary_color').addEventListener('input', function() {
        document.getElementById('secondary_color_text').value = this.value;
    });
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
?>
