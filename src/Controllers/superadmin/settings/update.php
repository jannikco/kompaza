<?php

use App\Models\Setting;

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/settings');
}

// Validate platform_email_service
$emailService = $_POST['platform_email_service'] ?? 'brevo';
if (!in_array($emailService, ['brevo', 'mailgun', 'smtp'])) {
    flashMessage('error', 'Invalid email service provider.');
    redirect('/settings');
}

$settingsToSave = [
    'platform_name'             => ['type' => 'text',     'desc' => 'Platform name'],
    'support_email'             => ['type' => 'text',     'desc' => 'Support email'],
    'default_trial_days'        => ['type' => 'number',   'desc' => 'Default trial duration (days)'],
    'maintenance_mode'          => ['type' => 'boolean',  'desc' => 'Maintenance mode'],
    'platform_email_service'    => ['type' => 'text',     'desc' => 'Platform email service provider'],
    'platform_brevo_api_key'    => ['type' => 'password', 'desc' => 'Platform Brevo API key'],
    'platform_brevo_list_id'    => ['type' => 'text',     'desc' => 'Platform Brevo list ID'],
    'platform_mailgun_api_key'  => ['type' => 'password', 'desc' => 'Platform Mailgun API key'],
    'platform_mailgun_domain'   => ['type' => 'text',     'desc' => 'Platform Mailgun domain'],
    'platform_smtp_host'        => ['type' => 'text',     'desc' => 'Platform SMTP host'],
    'platform_smtp_port'        => ['type' => 'number',   'desc' => 'Platform SMTP port'],
    'platform_smtp_username'    => ['type' => 'text',     'desc' => 'Platform SMTP username'],
    'platform_smtp_password'    => ['type' => 'password', 'desc' => 'Platform SMTP password'],
    'platform_smtp_encryption'  => ['type' => 'text',     'desc' => 'Platform SMTP encryption'],
    'platform_mail_from_address' => ['type' => 'text',    'desc' => 'Platform mail from address'],
    'platform_mail_from_name'   => ['type' => 'text',     'desc' => 'Platform mail from name'],
];

foreach ($settingsToSave as $key => $meta) {
    if (isset($_POST[$key])) {
        Setting::set($key, sanitize($_POST[$key]), null, $meta['type'], $meta['desc']);
    }
}

logAudit('settings_updated', 'settings');
flashMessage('success', 'Settings updated successfully.');
redirect('/settings');
