<?php

use App\Models\Article;

$tenant = currentTenant();
$tenantId = currentTenantId();

$article = Article::findBySlug($slug, $tenantId);

if (!$article) {
    http_response_code(404);
    view('errors/404');
    exit;
}

Article::incrementViews($article['id']);

view('shop/article', [
    'tenant' => $tenant,
    'article' => $article,
]);
