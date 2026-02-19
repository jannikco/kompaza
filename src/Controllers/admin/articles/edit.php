<?php

use App\Models\Article;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$article = Article::find($id, $tenantId);

if (!$article) {
    http_response_code(404);
    view('errors/404');
    exit;
}

view('admin/articles/edit', [
    'tenant' => currentTenant(),
    'article' => $article,
]);
