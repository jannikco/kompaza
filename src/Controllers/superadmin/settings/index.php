<?php

use App\Models\Setting;

$settings = Setting::allGlobal();

// Most recent settings update timestamp (platform scope), for display.
$lastUpdatedAt = null;
foreach ($settings as $s) {
    if (!empty($s['updated_at']) && ($lastUpdatedAt === null || $s['updated_at'] > $lastUpdatedAt)) {
        $lastUpdatedAt = $s['updated_at'];
    }
}

// Read-only platform integration status. These reflect server-level config
// (.env / config.php constants), not the editable settings stored in the DB.
$integrationStatus = [
    'stripe_secret'        => defined('STRIPE_SECRET_KEY') && STRIPE_SECRET_KEY !== '',
    'stripe_publishable'   => defined('STRIPE_PUBLISHABLE_KEY') && STRIPE_PUBLISHABLE_KEY !== '',
    'stripe_webhook'       => defined('STRIPE_WEBHOOK_SECRET') && STRIPE_WEBHOOK_SECRET !== '',
    'stripe_live'          => defined('STRIPE_SECRET_KEY') && strpos((string) STRIPE_SECRET_KEY, 'sk_live_') === 0,
    'cron_secret'          => defined('CRON_SECRET') && CRON_SECRET !== '',
    'app_debug'            => defined('APP_DEBUG') && APP_DEBUG === true,
    'app_env'              => defined('APP_ENV') ? APP_ENV : 'unknown',
    'brevo_key'            => defined('BREVO_API_KEY') && BREVO_API_KEY !== '',
    'openai_key'           => defined('OPENAI_API_KEY') && OPENAI_API_KEY !== '',
];

// Email provider in effect for the platform (DB setting overrides default).
$platformEmailProvider = 'brevo';
foreach ($settings as $s) {
    if ($s['setting_key'] === 'platform_email_service' && $s['setting_value'] !== '' && $s['setting_value'] !== null) {
        $platformEmailProvider = $s['setting_value'];
    }
}

view('superadmin/settings/index', [
    'settings'               => $settings,
    'lastUpdatedAt'          => $lastUpdatedAt,
    'integrationStatus'      => $integrationStatus,
    'platformEmailProvider'  => $platformEmailProvider,
]);
