<?php

use App\Models\Article;
use App\Models\Ebook;

$tenant = currentTenant();
$tenantId = currentTenantId();

$articles = Article::publishedByTenant($tenantId, 3);
$ebooks = Ebook::publishedByTenant($tenantId);
$ebooks = array_slice($ebooks, 0, 3);

view('shop/home', [
    'tenant' => $tenant,
    'articles' => $articles,
    'ebooks' => $ebooks,
]);
