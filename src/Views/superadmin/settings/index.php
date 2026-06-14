<?php
$pageTitle = 'Platform Settings';
$currentPage = 'settings';

// Build settings map
$settingsMap = [];
foreach ($settings as $s) {
    $settingsMap[$s['setting_key']] = $s['setting_value'];
}

$integrationStatus = $integrationStatus ?? [];
$platformEmailProvider = $platformEmailProvider ?? ($settingsMap['platform_email_service'] ?? 'brevo');
$lastUpdatedAt = $lastUpdatedAt ?? null;

$providerLabels = [
    'brevo'   => 'Brevo',
    'mailgun' => 'Mailgun',
    'smtp'    => 'Own SMTP Server',
];

// Helper closures for the read-only status panel.
$badgeOk = function (bool $ok, string $okLabel = 'Configured', string $missingLabel = 'Not set'): string {
    if ($ok) {
        return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-900/50 border border-green-700 text-green-300">'
            . '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
            . h($okLabel) . '</span>';
    }
    return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-900/50 border border-red-700 text-red-300">'
        . '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
        . h($missingLabel) . '</span>';
};

ob_start();
?>

<!-- ============================================ -->
<!-- Read-only: Platform integration status       -->
<!-- ============================================ -->
<div class="bg-gray-800 rounded-xl border border-gray-700 p-6 max-w-3xl mb-6">
    <div class="flex items-start justify-between mb-1">
        <h2 class="text-lg font-semibold text-white flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Platform Integration Status
        </h2>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-700 text-gray-300 border border-gray-600">Read-only</span>
    </div>
    <p class="text-sm text-gray-400 mb-5">Server-level configuration loaded from the environment. To change these values, update the <code class="text-gray-300 bg-gray-900 px-1.5 py-0.5 rounded">.env</code> file on the server and reload PHP-FPM.</p>

    <div class="space-y-3">
        <!-- Stripe secret key -->
        <div class="flex items-center justify-between py-2.5 px-3 rounded-lg bg-gray-900/40 border border-gray-700">
            <div>
                <p class="text-sm font-medium text-white">Stripe Platform Secret Key</p>
                <p class="text-xs text-gray-400">Used for platform billing &amp; subscription charges (<code class="text-gray-400">STRIPE_SECRET_KEY</code>)</p>
            </div>
            <div class="flex items-center gap-2">
                <?php if (!empty($integrationStatus['stripe_secret'])): ?>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?= !empty($integrationStatus['stripe_live']) ? 'bg-indigo-900/50 border border-indigo-700 text-indigo-300' : 'bg-yellow-900/50 border border-yellow-700 text-yellow-300' ?>">
                        <?= !empty($integrationStatus['stripe_live']) ? 'Live mode' : 'Test mode' ?>
                    </span>
                <?php endif; ?>
                <?= $badgeOk(!empty($integrationStatus['stripe_secret'])) ?>
            </div>
        </div>

        <!-- Stripe publishable key -->
        <div class="flex items-center justify-between py-2.5 px-3 rounded-lg bg-gray-900/40 border border-gray-700">
            <div>
                <p class="text-sm font-medium text-white">Stripe Publishable Key</p>
                <p class="text-xs text-gray-400">Client-side checkout key (<code class="text-gray-400">STRIPE_PUBLISHABLE_KEY</code>)</p>
            </div>
            <?= $badgeOk(!empty($integrationStatus['stripe_publishable'])) ?>
        </div>

        <!-- Stripe webhook secret -->
        <div class="flex items-center justify-between py-2.5 px-3 rounded-lg bg-gray-900/40 border border-gray-700">
            <div>
                <p class="text-sm font-medium text-white">Stripe Webhook Secret</p>
                <p class="text-xs text-gray-400">Verifies inbound Stripe webhooks (<code class="text-gray-400">STRIPE_WEBHOOK_SECRET</code>)</p>
            </div>
            <?= $badgeOk(!empty($integrationStatus['stripe_webhook'])) ?>
        </div>

        <!-- Email provider -->
        <div class="flex items-center justify-between py-2.5 px-3 rounded-lg bg-gray-900/40 border border-gray-700">
            <div>
                <p class="text-sm font-medium text-white">Platform Email Provider</p>
                <p class="text-xs text-gray-400">Provider used when tenants pick "Kompaza" as their email service</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-900/50 border border-indigo-700 text-indigo-300">
                <?= h($providerLabels[$platformEmailProvider] ?? ucfirst($platformEmailProvider)) ?>
            </span>
        </div>

        <!-- CRON secret -->
        <div class="flex items-center justify-between py-2.5 px-3 rounded-lg bg-gray-900/40 border border-gray-700">
            <div>
                <p class="text-sm font-medium text-white">Cron Secret</p>
                <p class="text-xs text-gray-400">Required to run scheduled <code class="text-gray-400">/api/cron/*</code> jobs (<code class="text-gray-400">CRON_SECRET</code>)</p>
            </div>
            <?= $badgeOk(!empty($integrationStatus['cron_secret'])) ?>
        </div>

        <!-- App debug -->
        <div class="flex items-center justify-between py-2.5 px-3 rounded-lg bg-gray-900/40 border border-gray-700">
            <div>
                <p class="text-sm font-medium text-white">Debug Mode</p>
                <p class="text-xs text-gray-400">Environment: <span class="text-gray-300 font-medium"><?= h((string) ($integrationStatus['app_env'] ?? 'unknown')) ?></span> (<code class="text-gray-400">APP_DEBUG</code>)</p>
            </div>
            <?php if (!empty($integrationStatus['app_debug'])): ?>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-900/50 border border-yellow-700 text-yellow-300">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg>
                    Enabled
                </span>
            <?php else: ?>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-700 border border-gray-600 text-gray-300">Disabled</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- Editable: Platform settings                  -->
<!-- ============================================ -->
<div class="bg-gray-800 rounded-xl border border-gray-700 p-6 max-w-2xl">
    <div class="flex items-start justify-between mb-6">
        <h2 class="text-lg font-semibold text-white">Platform Settings</h2>
        <?php if ($lastUpdatedAt): ?>
        <span class="text-xs text-gray-500">Last updated <?= h(formatDate($lastUpdatedAt, 'd M Y H:i')) ?></span>
        <?php endif; ?>
    </div>

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
                <input type="number" min="0" name="default_trial_days" value="<?= h($settingsMap['default_trial_days'] ?? '7') ?>" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
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
                        <input type="number" min="0" name="platform_smtp_port" value="<?= h($settingsMap['platform_smtp_port'] ?? '587') ?>" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="587">
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
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">Save Settings</button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/superadmin/layouts/layout.php';
?>
