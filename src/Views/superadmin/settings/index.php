<?php
$pageTitle = 'Platform Settings';
$currentPage = 'settings';

// Build settings map
$settingsMap = [];
foreach ($settings as $s) {
    $settingsMap[$s['setting_key']] = $s['setting_value'];
}

ob_start();
?>

<div class="bg-gray-800 rounded-xl border border-gray-700 p-6 max-w-2xl">
    <h2 class="text-lg font-semibold text-white mb-6">Platform Settings</h2>

    <?php $flash = getFlashMessage(); ?>
    <?php if ($flash): ?>
    <div class="mb-4 p-3 rounded-lg <?= $flash['type'] === 'success' ? 'bg-green-900/50 border border-green-700 text-green-300' : 'bg-red-900/50 border border-red-700 text-red-300' ?>">
        <?= h($flash['message']) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/settings/update" class="space-y-6">
        <?= csrfField() ?>

        <!-- General Settings -->
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Platform Name</label>
                <input type="text" name="platform_name" value="<?= h($settingsMap['platform_name'] ?? 'Kompaza') ?>" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Support Email</label>
                <input type="email" name="support_email" value="<?= h($settingsMap['support_email'] ?? '') ?>" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Default Trial Days</label>
                <input type="number" name="default_trial_days" value="<?= h($settingsMap['default_trial_days'] ?? '7') ?>" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="maintenance_mode" value="0">
                <input type="checkbox" name="maintenance_mode" id="maintenance_mode" value="1" <?= !empty($settingsMap['maintenance_mode']) ? 'checked' : '' ?> class="rounded bg-gray-700 border-gray-600 text-indigo-600">
                <label for="maintenance_mode" class="text-sm text-gray-300">Maintenance Mode</label>
            </div>
        </div>

        <!-- Email Service Section -->
        <div class="border-t border-gray-700 pt-6" x-data="{ emailService: '<?= h($settingsMap['platform_email_service'] ?? 'brevo') ?>' }">
            <h3 class="text-md font-semibold text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Platform Email Service
            </h3>
            <p class="text-sm text-gray-400 mb-4">Configure the email provider used when tenants select "Kompaza" as their email service.</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Email Provider</label>
                    <select name="platform_email_service" x-model="emailService" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="brevo">Brevo</option>
                        <option value="mailgun">Mailgun</option>
                        <option value="smtp">Own SMTP Server</option>
                    </select>
                </div>

                <!-- Brevo fields -->
                <div x-show="emailService === 'brevo'" x-cloak class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Brevo API Key</label>
                        <input type="password" name="platform_brevo_api_key" value="<?= h($settingsMap['platform_brevo_api_key'] ?? (defined('BREVO_API_KEY') ? BREVO_API_KEY : '')) ?>" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="xkeysib-...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Brevo List ID</label>
                        <input type="text" name="platform_brevo_list_id" value="<?= h($settingsMap['platform_brevo_list_id'] ?? '') ?>" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="e.g., 3">
                    </div>
                </div>

                <!-- Mailgun fields -->
                <div x-show="emailService === 'mailgun'" x-cloak class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Mailgun API Key</label>
                        <input type="password" name="platform_mailgun_api_key" value="<?= h($settingsMap['platform_mailgun_api_key'] ?? '') ?>" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="key-...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Mailgun Domain</label>
                        <input type="text" name="platform_mailgun_domain" value="<?= h($settingsMap['platform_mailgun_domain'] ?? '') ?>" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="mg.example.com">
                    </div>
                </div>

                <!-- SMTP fields -->
                <div x-show="emailService === 'smtp'" x-cloak class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">SMTP Host</label>
                        <input type="text" name="platform_smtp_host" value="<?= h($settingsMap['platform_smtp_host'] ?? '') ?>" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="smtp.example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">SMTP Port</label>
                        <input type="number" name="platform_smtp_port" value="<?= h($settingsMap['platform_smtp_port'] ?? '587') ?>" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="587">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">SMTP Username</label>
                        <input type="text" name="platform_smtp_username" value="<?= h($settingsMap['platform_smtp_username'] ?? '') ?>" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="user@example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">SMTP Password</label>
                        <input type="password" name="platform_smtp_password" value="<?= h($settingsMap['platform_smtp_password'] ?? '') ?>" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="Password">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Encryption</label>
                        <select name="platform_smtp_encryption" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            <option value="tls" <?= ($settingsMap['platform_smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (Recommended)</option>
                            <option value="ssl" <?= ($settingsMap['platform_smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                            <option value="none" <?= ($settingsMap['platform_smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                        </select>
                    </div>
                </div>

                <!-- Mail From fields (all providers) -->
                <div class="border-t border-gray-600 pt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Mail From Address</label>
                        <input type="email" name="platform_mail_from_address" value="<?= h($settingsMap['platform_mail_from_address'] ?? (defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : '')) ?>" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="info@kompaza.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Mail From Name</label>
                        <input type="text" name="platform_mail_from_name" value="<?= h($settingsMap['platform_mail_from_name'] ?? (defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : '')) ?>" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="Kompaza">
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium">Save Settings</button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/superadmin/layouts/layout.php';
?>
