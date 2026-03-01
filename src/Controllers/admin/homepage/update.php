<?php

use App\Models\Tenant;

if (!isPost()) redirect('/admin/homepage');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/homepage');
}

$tenantId = currentTenantId();

// Validate homepage_template
$homepageTemplate = $_POST['homepage_template'] ?? 'starter';
if (!in_array($homepageTemplate, ['starter', 'bold', 'elegant'])) {
    $homepageTemplate = 'starter';
}

// Parse homepage_sections JSON
$jsonStr = $_POST['homepage_sections_json'] ?? '';

// Size limit: 500KB
if (strlen($jsonStr) > 512000) {
    flashMessage('error', 'Homepage configuration is too large.');
    redirect('/admin/homepage');
}

$config = json_decode($jsonStr, true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($config)) {
    flashMessage('error', 'Invalid homepage configuration data.');
    redirect('/admin/homepage');
}

// Validate hero CTA URLs
$hero = $config['hero'] ?? [];
foreach (['cta_primary_url', 'cta_secondary_url'] as $urlKey) {
    $url = $hero[$urlKey] ?? '';
    if (!empty($url)) {
        // Must start with / or http(s)://, reject javascript:
        if (!preg_match('#^(/|https?://)#i', $url) || stripos($url, 'javascript:') !== false) {
            flashMessage('error', 'Invalid CTA URL. Must start with / or http://');
            redirect('/admin/homepage');
        }
    }
}

// Sanitize hero text
$config['hero'] = [
    'cta_primary_text' => htmlspecialchars(trim($hero['cta_primary_text'] ?? ''), ENT_QUOTES, 'UTF-8'),
    'cta_primary_url' => trim($hero['cta_primary_url'] ?? ''),
    'cta_secondary_text' => htmlspecialchars(trim($hero['cta_secondary_text'] ?? ''), ENT_QUOTES, 'UTF-8'),
    'cta_secondary_url' => trim($hero['cta_secondary_url'] ?? ''),
];

// Validate and sanitize sections
$allowedTypes = ['articles', 'ebooks', 'products', 'courses', 'newsletter', 'richtext', 'trust_strip'];
$sections = $config['sections'] ?? [];
$sanitizedSections = [];
$richtextCount = 0;

foreach ($sections as $sec) {
    $type = $sec['type'] ?? '';
    if (!in_array($type, $allowedTypes)) continue;

    if ($type === 'richtext') {
        $richtextCount++;
        if ($richtextCount > 10) continue; // Max 10 richtext sections
    }

    $sanitized = [
        'id' => preg_replace('/[^a-zA-Z0-9_]/', '', $sec['id'] ?? ('sec_' . bin2hex(random_bytes(4)))),
        'type' => $type,
        'enabled' => !empty($sec['enabled']),
        'heading' => htmlspecialchars(trim($sec['heading'] ?? ''), ENT_QUOTES, 'UTF-8'),
    ];

    if (isset($sec['subtitle'])) {
        $sanitized['subtitle'] = htmlspecialchars(trim($sec['subtitle'] ?? ''), ENT_QUOTES, 'UTF-8');
    }

    if (isset($sec['count'])) {
        $sanitized['count'] = max(1, min(12, (int)$sec['count']));
    }

    // Richtext body: strip dangerous tags/attributes
    if ($type === 'richtext' && isset($sec['body'])) {
        $body = $sec['body'];
        // Strip script, iframe, object, embed tags
        $body = preg_replace('#<(script|iframe|object|embed|form|input|textarea|select|button|link|meta|base|applet)[^>]*>.*?</\1>#si', '', $body);
        $body = preg_replace('#<(script|iframe|object|embed|form|input|textarea|select|button|link|meta|base|applet)[^>]*/?\s*>#si', '', $body);
        // Strip on* event attributes
        $body = preg_replace('#\s+on\w+\s*=\s*["\'][^"\']*["\']#i', '', $body);
        $body = preg_replace('#\s+on\w+\s*=\s*\S+#i', '', $body);
        // Strip javascript: in href/src
        $body = preg_replace('#(href|src)\s*=\s*["\']?\s*javascript:#i', '$1="', $body);
        $sanitized['body'] = $body;
    }

    $sanitizedSections[] = $sanitized;
}

$config['sections'] = $sanitizedSections;
$jsonOutput = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$data = [
    'homepage_template' => $homepageTemplate,
    'homepage_sections' => $jsonOutput,
];

Tenant::update($tenantId, $data);

logAudit('homepage_updated', 'tenant', $tenantId);
flashMessage('success', 'Homepage updated successfully.');
redirect('/admin/homepage');
