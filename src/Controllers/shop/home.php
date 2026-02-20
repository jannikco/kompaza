<?php

use App\Models\Article;
use App\Models\Ebook;
use App\Models\CustomPage;

$tenant = currentTenant();
$tenantId = currentTenantId();

// Check for custom homepage override
$customHomepage = CustomPage::getHomepage($tenantId);
if ($customHomepage) {
    CustomPage::incrementViews($customHomepage['id']);
    if ($customHomepage['layout'] === 'full') {
        echo $customHomepage['content'];
        exit;
    }
    // Shop layout homepage
    view('shop/custom-page', [
        'tenant' => $tenant,
        'page' => $customHomepage,
    ]);
    exit;
}

$articles = Article::publishedByTenant($tenantId, 3);
$ebooks = Ebook::publishedByTenant($tenantId);
$ebooks = array_slice($ebooks, 0, 3);

view('shop/home', [
    'tenant' => $tenant,
    'articles' => $articles,
    'ebooks' => $ebooks,
]);
