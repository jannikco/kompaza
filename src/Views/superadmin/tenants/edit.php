<?php
$pageTitle = 'Edit Tenant: ' . ($tenant['name'] ?? '');
$currentPage = 'tenants';
ob_start();

$inputClass = 'w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent';
$labelClass = 'block text-sm font-medium text-gray-300 mb-1';

$featureLabels = [
    'feature_blog'          => 'Blog / Articles',
    'feature_ebooks'        => 'Ebooks',
    'feature_lead_magnets'  => 'Lead Magnets',
    'feature_orders'        => 'Orders / Shop',
    'feature_connectpilot'  => 'ConnectPilot (LinkedIn)',
    'feature_courses'       => 'Courses',
    'feature_newsletters'   => 'Newsletters',
    'feature_consultations' => 'Consultations',
    'feature_mastermind'    => 'Mastermind',
    'feature_custom_pages'  => 'Custom Pages',
    'feature_memberships'   => 'Memberships',
    'feature_prompts'       => 'Prompt Library',
    'feature_community'     => 'Community',
];

$emailService = $tenant['email_service'] ?? 'kompaza';
?>

<div class="max-w-4xl" x-data="{ tab: 'identity', emailService: '<?= h($emailService) ?>' }">
    <!-- Back + header -->
    <div class="flex items-center justify-between mb-6">
        <a href="/tenants/show?id=<?= (int)$tenant['id'] ?>" class="text-sm text-gray-400 hover:text-white inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Tenant
        </a>
        <a href="https://<?= h($tenant['slug']) ?>.<?= PLATFORM_DOMAIN ?>/admin" target="_blank" rel="noopener" class="text-sm text-indigo-400 hover:text-indigo-300 inline-flex items-center">
            Open Admin
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>
    </div>

    <form method="POST" action="/tenants/update">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= (int)$tenant['id'] ?>">

        <!-- Tabs -->
        <div class="border-b border-gray-700 mb-6">
            <nav class="flex flex-wrap gap-1 -mb-px">
                <?php
                $tabs = ['identity' => 'Identity', 'features' => 'Features', 'branding' => 'Branding', 'integrations' => 'Integrations', 'pricing' => 'Pricing'];
                foreach ($tabs as $key => $label):
                ?>
                <button type="button" @click="tab = '<?= $key ?>'"
                    :class="tab === '<?= $key ?>' ? 'border-indigo-500 text-white' : 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-600'"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 transition"><?= $label ?></button>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- IDENTITY -->
        <div x-show="tab === 'identity'" class="bg-gray-800 rounded-xl border border-gray-700 p-6 space-y-5">
            <div>
                <label for="name" class="<?= $labelClass ?>">Tenant Name</label>
                <input type="text" id="name" name="name" required value="<?= h($tenant['name']) ?>" class="<?= $inputClass ?>">
            </div>
            <div>
                <label for="slug" class="<?= $labelClass ?>">Slug</label>
                <div class="flex items-center">
                    <input type="text" id="slug" name="slug" required value="<?= h($tenant['slug']) ?>"
                        class="flex-1 px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-l-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <span class="px-4 py-2.5 bg-gray-600 border border-gray-600 rounded-r-lg text-gray-400 text-sm">.<?= PLATFORM_DOMAIN ?></span>
                </div>
            </div>
            <div>
                <label for="email" class="<?= $labelClass ?>">Contact Email</label>
                <input type="email" id="email" name="email" value="<?= h($tenant['email'] ?? '') ?>" class="<?= $inputClass ?>">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="status" class="<?= $labelClass ?>">Status</label>
                    <select id="status" name="status" class="<?= $inputClass ?>">
                        <?php foreach (['trial' => 'Trial', 'active' => 'Active', 'suspended' => 'Suspended', 'cancelled' => 'Cancelled'] as $sv => $sl): ?>
                        <option value="<?= $sv ?>" <?= ($tenant['status'] ?? '') === $sv ? 'selected' : '' ?>><?= $sl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="plan_id" class="<?= $labelClass ?>">Plan</label>
                    <select id="plan_id" name="plan_id" class="<?= $inputClass ?>">
                        <option value="">No plan</option>
                        <?php foreach ($plans as $plan): ?>
                        <option value="<?= (int)$plan['id'] ?>" <?= ($tenant['plan_id'] ?? '') == $plan['id'] ? 'selected' : '' ?>><?= h($plan['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div>
                <label for="trial_ends_at" class="<?= $labelClass ?>">Trial Ends</label>
                <input type="date" id="trial_ends_at" name="trial_ends_at"
                    value="<?= !empty($tenant['trial_ends_at']) ? date('Y-m-d', strtotime($tenant['trial_ends_at'])) : '' ?>" class="<?= $inputClass ?>">
            </div>
        </div>

        <!-- FEATURES -->
        <div x-show="tab === 'features'" x-cloak class="bg-gray-800 rounded-xl border border-gray-700 p-6">
            <p class="text-sm text-gray-400 mb-4">Toggle which platform modules this tenant can access.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <?php foreach ($featureLabels as $col => $label): ?>
                <label class="flex items-center gap-3 p-3 bg-gray-900/40 border border-gray-700 rounded-lg cursor-pointer hover:border-gray-600">
                    <input type="checkbox" name="<?= $col ?>" value="1" <?= !empty($tenant[$col]) ? 'checked' : '' ?>
                        class="w-4 h-4 rounded bg-gray-700 border-gray-600 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-gray-800">
                    <span class="text-sm text-gray-200"><?= h($label) ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- BRANDING -->
        <div x-show="tab === 'branding'" x-cloak class="bg-gray-800 rounded-xl border border-gray-700 p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="primary_color" class="<?= $labelClass ?>">Primary Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="primary_color" name="primary_color" value="<?= h($tenant['primary_color'] ?? '#3b82f6') ?>"
                            class="h-11 w-14 rounded-lg bg-gray-700 border border-gray-600 cursor-pointer">
                        <input type="text" value="<?= h($tenant['primary_color'] ?? '#3b82f6') ?>" disabled class="flex-1 px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-gray-400">
                    </div>
                </div>
                <div>
                    <label for="secondary_color" class="<?= $labelClass ?>">Secondary Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="secondary_color" name="secondary_color" value="<?= h($tenant['secondary_color'] ?? '#6366f1') ?>"
                            class="h-11 w-14 rounded-lg bg-gray-700 border border-gray-600 cursor-pointer">
                        <input type="text" value="<?= h($tenant['secondary_color'] ?? '#6366f1') ?>" disabled class="flex-1 px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-gray-400">
                    </div>
                </div>
            </div>
            <div>
                <label for="logo_url" class="<?= $labelClass ?>">Logo URL</label>
                <input type="text" id="logo_url" name="logo_url" value="<?= h($tenant['logo_url'] ?? '') ?>" placeholder="https://..." class="<?= $inputClass ?>">
            </div>
            <div>
                <label for="custom_domain" class="<?= $labelClass ?>">Custom Domain</label>
                <input type="text" id="custom_domain" name="custom_domain" value="<?= h($tenant['custom_domain'] ?? '') ?>" placeholder="shop.example.com" class="<?= $inputClass ?>">
            </div>
            <div>
                <label for="homepage_template" class="<?= $labelClass ?>">Homepage Template</label>
                <select id="homepage_template" name="homepage_template" class="<?= $inputClass ?>">
                    <?php $currentTpl = $tenant['homepage_template'] ?? 'starter'; ?>
                    <?php foreach (['starter' => 'Starter', 'classic' => 'Classic', 'bold' => 'Bold', 'minimal' => 'Minimal'] as $tv => $tl): ?>
                    <option value="<?= $tv ?>" <?= $currentTpl === $tv ? 'selected' : '' ?>><?= $tl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="custom_css" class="<?= $labelClass ?>">Custom CSS</label>
                <textarea id="custom_css" name="custom_css" rows="5" placeholder=".hero { ... }"
                    class="<?= $inputClass ?> font-mono text-xs"><?= h($tenant['custom_css'] ?? '') ?></textarea>
            </div>
            <div>
                <label for="custom_footer_html" class="<?= $labelClass ?>">Custom Footer HTML</label>
                <textarea id="custom_footer_html" name="custom_footer_html" rows="4" placeholder="<div>...</div>"
                    class="<?= $inputClass ?> font-mono text-xs"><?= h($tenant['custom_footer_html'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- INTEGRATIONS -->
        <div x-show="tab === 'integrations'" x-cloak class="bg-gray-800 rounded-xl border border-gray-700 p-6 space-y-5">
            <div>
                <label for="email_service" class="<?= $labelClass ?>">Email Service</label>
                <select id="email_service" name="email_service" x-model="emailService" class="<?= $inputClass ?>">
                    <?php foreach (['kompaza' => 'Kompaza (platform default)', 'brevo' => 'Brevo', 'mailgun' => 'Mailgun', 'smtp' => 'Custom SMTP'] as $ev => $el): ?>
                    <option value="<?= $ev ?>" <?= $emailService === $ev ? 'selected' : '' ?>><?= $el ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Brevo -->
            <div x-show="emailService === 'brevo'" x-cloak>
                <label for="brevo_api_key" class="<?= $labelClass ?>">Brevo API Key</label>
                <input type="text" id="brevo_api_key" name="brevo_api_key" value="<?= h($tenant['brevo_api_key'] ?? '') ?>" autocomplete="off" class="<?= $inputClass ?>">
            </div>

            <!-- Mailgun -->
            <div x-show="emailService === 'mailgun'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="mailgun_api_key" class="<?= $labelClass ?>">Mailgun API Key</label>
                    <input type="text" id="mailgun_api_key" name="mailgun_api_key" value="<?= h($tenant['mailgun_api_key'] ?? '') ?>" autocomplete="off" class="<?= $inputClass ?>">
                </div>
                <div>
                    <label for="mailgun_domain" class="<?= $labelClass ?>">Mailgun Domain</label>
                    <input type="text" id="mailgun_domain" name="mailgun_domain" value="<?= h($tenant['mailgun_domain'] ?? '') ?>" class="<?= $inputClass ?>">
                </div>
            </div>

            <!-- SMTP -->
            <div x-show="emailService === 'smtp'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="smtp_host" class="<?= $labelClass ?>">SMTP Host</label>
                    <input type="text" id="smtp_host" name="smtp_host" value="<?= h($tenant['smtp_host'] ?? '') ?>" class="<?= $inputClass ?>">
                </div>
                <div>
                    <label for="smtp_port" class="<?= $labelClass ?>">SMTP Port</label>
                    <input type="number" id="smtp_port" name="smtp_port" value="<?= h((string)($tenant['smtp_port'] ?? 587)) ?>" class="<?= $inputClass ?>">
                </div>
                <div>
                    <label for="smtp_username" class="<?= $labelClass ?>">SMTP Username</label>
                    <input type="text" id="smtp_username" name="smtp_username" value="<?= h($tenant['smtp_username'] ?? '') ?>" autocomplete="off" class="<?= $inputClass ?>">
                </div>
                <div>
                    <label for="smtp_password" class="<?= $labelClass ?>">SMTP Password</label>
                    <input type="password" id="smtp_password" name="smtp_password" value="<?= h($tenant['smtp_password'] ?? '') ?>" autocomplete="new-password" class="<?= $inputClass ?>">
                </div>
                <div>
                    <label for="smtp_encryption" class="<?= $labelClass ?>">Encryption</label>
                    <select id="smtp_encryption" name="smtp_encryption" class="<?= $inputClass ?>">
                        <?php $enc = $tenant['smtp_encryption'] ?? 'tls'; ?>
                        <?php foreach (['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None'] as $encV => $encL): ?>
                        <option value="<?= $encV ?>" <?= $enc === $encV ? 'selected' : '' ?>><?= $encL ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="pt-2 border-t border-gray-700">
                <h4 class="text-sm font-semibold text-white mb-3 mt-3">Stripe</h4>
                <div class="space-y-5">
                    <div>
                        <label for="stripe_publishable_key" class="<?= $labelClass ?>">Publishable Key</label>
                        <input type="text" id="stripe_publishable_key" name="stripe_publishable_key" value="<?= h($tenant['stripe_publishable_key'] ?? '') ?>" autocomplete="off" class="<?= $inputClass ?>">
                    </div>
                    <div>
                        <label for="stripe_secret_key" class="<?= $labelClass ?>">Secret Key</label>
                        <input type="password" id="stripe_secret_key" name="stripe_secret_key" value="<?= h($tenant['stripe_secret_key'] ?? '') ?>" autocomplete="new-password" class="<?= $inputClass ?>">
                    </div>
                    <div>
                        <label for="stripe_webhook_secret" class="<?= $labelClass ?>">Webhook Secret</label>
                        <input type="password" id="stripe_webhook_secret" name="stripe_webhook_secret" value="<?= h($tenant['stripe_webhook_secret'] ?? '') ?>" autocomplete="new-password" class="<?= $inputClass ?>">
                    </div>
                </div>
            </div>

            <div class="pt-2 border-t border-gray-700">
                <label for="google_analytics_id" class="<?= $labelClass ?> mt-3">Google Analytics ID</label>
                <input type="text" id="google_analytics_id" name="google_analytics_id" value="<?= h($tenant['google_analytics_id'] ?? '') ?>" placeholder="G-XXXXXXX" class="<?= $inputClass ?>">
            </div>
        </div>

        <!-- PRICING -->
        <div x-show="tab === 'pricing'" x-cloak class="bg-gray-800 rounded-xl border border-gray-700 p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="currency" class="<?= $labelClass ?>">Currency</label>
                    <select id="currency" name="currency" class="<?= $inputClass ?>">
                        <?php $cur = $tenant['currency'] ?? 'DKK'; ?>
                        <?php foreach (['DKK', 'EUR', 'USD', 'GBP', 'SEK', 'NOK'] as $c): ?>
                        <option value="<?= $c ?>" <?= $cur === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="tax_rate" class="<?= $labelClass ?>">Tax Rate (%)</label>
                    <input type="number" step="0.01" min="0" max="100" id="tax_rate" name="tax_rate" value="<?= h(number_format((float)($tenant['tax_rate'] ?? 25), 2, '.', '')) ?>" class="<?= $inputClass ?>">
                </div>
            </div>
        </div>

        <!-- Save bar -->
        <div class="flex items-center justify-end gap-3 mt-6">
            <a href="/tenants/show?id=<?= (int)$tenant['id'] ?>" class="px-4 py-2.5 text-sm font-medium text-gray-300 hover:text-white transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-lg transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-800">
                Save Changes
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/superadmin/layouts/layout.php';
?>
