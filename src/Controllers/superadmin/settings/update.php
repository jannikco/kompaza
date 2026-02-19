<?php

use App\Models\Setting;

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/settings');
}

$settingsToSave = [
    'platform_name' => ['type' => 'text', 'desc' => 'Platform name'],
    'support_email' => ['type' => 'text', 'desc' => 'Support email'],
    'default_trial_days' => ['type' => 'number', 'desc' => 'Default trial duration (days)'],
    'maintenance_mode' => ['type' => 'boolean', 'desc' => 'Maintenance mode'],
];

foreach ($settingsToSave as $key => $meta) {
    if (isset($_POST[$key])) {
        Setting::set($key, sanitize($_POST[$key]), null, $meta['type'], $meta['desc']);
    }
}

logAudit('settings_updated', 'settings');
flashMessage('success', 'Settings updated successfully.');
redirect('/settings');
