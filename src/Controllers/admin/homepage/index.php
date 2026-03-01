<?php

$tenant = currentTenant();
$template = $tenant['homepage_template'] ?? 'starter';

// Parse existing config or build defaults
$homepageConfig = null;
if (!empty($tenant['homepage_sections'])) {
    $homepageConfig = json_decode($tenant['homepage_sections'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $homepageConfig = null;
    }
}

// Hero CTA defaults
$heroConfig = $homepageConfig['hero'] ?? [
    'cta_primary_text' => tenantFeature('orders') ? 'Browse Products' : '',
    'cta_primary_url' => tenantFeature('orders') ? '/produkter' : '',
    'cta_secondary_text' => tenantFeature('blog') ? 'Read Our Blog' : (tenantFeature('ebooks') ? 'Browse Ebooks' : ''),
    'cta_secondary_url' => tenantFeature('blog') ? '/blog' : (tenantFeature('ebooks') ? '/eboger' : ''),
];

// Section defaults
if (!empty($homepageConfig['sections'])) {
    $sections = $homepageConfig['sections'];
} else {
    $sections = [];

    if ($template === 'bold') {
        $sections[] = ['id' => 'sec_1', 'type' => 'trust_strip', 'enabled' => true, 'heading' => 'Trust Strip'];
    }

    $sections[] = ['id' => 'sec_2', 'type' => 'articles', 'enabled' => tenantFeature('blog'), 'heading' => 'Latest Articles', 'subtitle' => '', 'count' => 3];
    $sections[] = ['id' => 'sec_3', 'type' => 'ebooks', 'enabled' => tenantFeature('ebooks'), 'heading' => 'Featured Ebooks', 'subtitle' => '', 'count' => 3];
    $sections[] = ['id' => 'sec_4', 'type' => 'courses', 'enabled' => tenantFeature('courses'), 'heading' => 'Our Courses', 'subtitle' => '', 'count' => 3];
    $sections[] = ['id' => 'sec_5', 'type' => 'products', 'enabled' => tenantFeature('orders'), 'heading' => 'Our Products', 'subtitle' => '', 'count' => 3];
    $sections[] = ['id' => 'sec_6', 'type' => 'newsletter', 'enabled' => true, 'heading' => 'Stay Updated', 'subtitle' => 'Subscribe to our newsletter and never miss new content and offers.'];
}

$pageTitle = 'Homepage Editor';
$currentPage = 'homepage';
ob_start();
include VIEWS_PATH . '/admin/homepage/index.php';
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layouts/admin-layout.php';
