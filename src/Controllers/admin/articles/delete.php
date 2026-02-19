<?php

use App\Models\Article;

if (!isPost()) redirect('/admin/articles');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/articles');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/articles');

$article = Article::find($id, $tenantId);
if (!$article) {
    flashMessage('error', 'Artikel ikke fundet.');
    redirect('/admin/articles');
}

// Delete associated image
if ($article['featured_image']) {
    $imgPath = PUBLIC_PATH . $article['featured_image'];
    if (file_exists($imgPath)) unlink($imgPath);
}

Article::delete($id, $tenantId);

logAudit('article_deleted', 'article', $id);
flashMessage('success', 'Artikel slettet.');
redirect('/admin/articles');
