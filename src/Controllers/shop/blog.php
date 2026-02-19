<?php

use App\Models\Article;

$tenant = currentTenant();
$tenantId = currentTenantId();

$articles = Article::publishedByTenant($tenantId);

view('shop/blog', [
    'tenant' => $tenant,
    'articles' => $articles,
]);
