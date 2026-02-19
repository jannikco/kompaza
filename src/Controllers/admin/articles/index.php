<?php

use App\Models\Article;

$tenantId = currentTenantId();
$articles = Article::allByTenant($tenantId);

view('admin/articles/index', [
    'tenant' => currentTenant(),
    'articles' => $articles,
]);
