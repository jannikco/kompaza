<?php
$pageTitle = 'Home';
$tenant = currentTenant();
$companyName = $tenant['company_name'] ?? $tenant['name'] ?? 'Store';
$metaDescription = $tenant['tagline'] ?? "Welcome to {$companyName}";

// Load the selected homepage template
$template = $tenant['homepage_template'] ?? 'starter';
$allowedTemplates = ['starter', 'bold', 'elegant'];
if (!in_array($template, $allowedTemplates)) {
    $template = 'starter';
}

// Parse homepage_sections JSON config
$homepageConfig = null;
if (!empty($tenant['homepage_sections'])) {
    $homepageConfig = json_decode($tenant['homepage_sections'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $homepageConfig = null;
    }
}

// Hero CTA config
$heroConfig = $homepageConfig['hero'] ?? [];

// Build sections from config or defaults
if (!empty($homepageConfig['sections'])) {
    $sections = $homepageConfig['sections'];
} else {
    // Default sections based on template (backward compatibility)
    $sections = [];

    if ($template === 'bold') {
        $sections[] = ['id' => 'sec_1', 'type' => 'trust_strip', 'enabled' => true, 'heading' => ''];
    }

    if (tenantFeature('blog')) {
        $sections[] = ['id' => 'sec_2', 'type' => 'articles', 'enabled' => true, 'heading' => 'Latest Articles', 'subtitle' => '', 'count' => 3];
    }
    if (tenantFeature('ebooks')) {
        $sections[] = ['id' => 'sec_3', 'type' => 'ebooks', 'enabled' => true, 'heading' => 'Featured Ebooks', 'subtitle' => '', 'count' => 3];
    }
    if (tenantFeature('courses')) {
        $sections[] = ['id' => 'sec_4', 'type' => 'courses', 'enabled' => true, 'heading' => 'Our Courses', 'subtitle' => '', 'count' => 3];
    }
    if (tenantFeature('orders')) {
        $sections[] = ['id' => 'sec_5', 'type' => 'products', 'enabled' => true, 'heading' => 'Our Products', 'subtitle' => '', 'count' => 3];
    }

    $sections[] = ['id' => 'sec_6', 'type' => 'newsletter', 'enabled' => true, 'heading' => 'Stay Updated', 'subtitle' => 'Subscribe to our newsletter and never miss new content and offers.'];
}

// Determine which content types are needed and load data conditionally
$enabledTypes = array_column(array_filter($sections, fn($s) => !empty($s['enabled'])), 'type');

$articles = [];
if (in_array('articles', $enabledTypes) || in_array('trust_strip', $enabledTypes)) {
    $articles = \App\Models\Article::publishedByTenant($tenant['id'], 3);
}

$ebooks = [];
if (in_array('ebooks', $enabledTypes) || in_array('trust_strip', $enabledTypes)) {
    $ebooks = \App\Models\Ebook::publishedByTenant($tenant['id']);
    $ebooks = array_slice($ebooks, 0, 3);
}

$courses = [];
if (in_array('courses', $enabledTypes) || in_array('trust_strip', $enabledTypes)) {
    $courses = \App\Models\Course::publishedByTenant($tenant['id'], 3);
}

$products = [];
if (in_array('products', $enabledTypes) || in_array('trust_strip', $enabledTypes)) {
    $products = \App\Models\Product::publishedByTenant($tenant['id'], 3);
}

ob_start();

$templateFile = VIEWS_PATH . "/shop/home-templates/{$template}.php";
if (!file_exists($templateFile)) {
    $templateFile = VIEWS_PATH . '/shop/home-templates/starter.php';
}
include $templateFile;

$content = ob_get_clean();
include VIEWS_PATH . '/shop/layout.php';
