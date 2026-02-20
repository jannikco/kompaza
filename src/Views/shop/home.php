<?php
$pageTitle = 'Home';
$tenant = currentTenant();
$companyName = $tenant['company_name'] ?? $tenant['name'] ?? 'Store';
$metaDescription = $tenant['tagline'] ?? "Welcome to {$companyName}";

// Load content data used by all templates
$articles = [];
if (tenantFeature('blog')) {
    $articles = \App\Models\Article::publishedByTenant($tenant['id'], 3);
}

$ebooks = [];
if (tenantFeature('ebooks')) {
    $ebooks = \App\Models\Ebook::publishedByTenant($tenant['id']);
    $ebooks = array_slice($ebooks, 0, 3);
}

$courses = [];
if (tenantFeature('courses')) {
    $courses = \App\Models\Course::publishedByTenant($tenant['id'], 3);
}

$products = [];
if (tenantFeature('orders')) {
    $products = \App\Models\Product::publishedByTenant($tenant['id'], 3);
}

ob_start();

// Load the selected homepage template
$template = $tenant['homepage_template'] ?? 'starter';
$allowedTemplates = ['starter', 'bold', 'elegant'];
if (!in_array($template, $allowedTemplates)) {
    $template = 'starter';
}
$templateFile = VIEWS_PATH . "/shop/home-templates/{$template}.php";
if (!file_exists($templateFile)) {
    $templateFile = VIEWS_PATH . '/shop/home-templates/starter.php';
}
include $templateFile;

$content = ob_get_clean();
include VIEWS_PATH . '/shop/layout.php';
