<?php

use App\Models\Article;

if (!isPost()) redirect('/admin/artikler');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/artikler');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/artikler');

$article = Article::find($id, $tenantId);
if (!$article) {
    flashMessage('error', 'Artikel ikke fundet.');
    redirect('/admin/artikler');
}

// Delete associated image
if ($article['featured_image']) {
    $imgPath = PUBLIC_PATH . $article['featured_image'];
    if (file_exists($imgPath)) unlink($imgPath);
}

Article::delete($id, $tenantId);

logAudit('article_deleted', 'article', $id);
flashMessage('success', 'Artikel slettet.');
redirect('/admin/artikler');
