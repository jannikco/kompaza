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

$status = sanitize($_POST['status'] ?? 'draft');

// Set published_at if publishing for the first time
$publishedAt = $_POST['published_at'] ?? $article['published_at'];
if ($status === 'published' && !$publishedAt) {
    $publishedAt = date('Y-m-d H:i:s');
}

$data = [
    'title' => sanitize($_POST['title'] ?? ''),
    'slug' => sanitize($_POST['slug'] ?? '') ?: slugify($_POST['title'] ?? ''),
    'excerpt' => sanitize($_POST['excerpt'] ?? ''),
    'content' => $_POST['content'] ?? null,
    'meta_description' => sanitize($_POST['meta_description'] ?? ''),
    'category' => sanitize($_POST['category'] ?? ''),
    'tags' => !empty($_POST['tags']) ? sanitize($_POST['tags']) : null,
    'status' => $status,
    'published_at' => $publishedAt,
    'author_name' => sanitize($_POST['author_name'] ?? ''),
];

// Handle featured image replacement
if (!empty($_FILES['featured_image']['name']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        flashMessage('error', 'Kun billeder (jpg, png, webp, gif) er tilladt.');
        redirect('/admin/artikler/edit?id=' . $id);
    }
    // Delete old image
    if ($article['featured_image']) {
        deleteUploadedFile($article['featured_image']);
    }
    $data['featured_image'] = uploadPublicFile($_FILES['featured_image']['tmp_name'], 'articles', 'art', $ext);
}

Article::update($id, $data);

logAudit('article_updated', 'article', $id);
flashMessage('success', 'Artikel opdateret.');
redirect('/admin/artikler');
