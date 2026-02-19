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
    $imgOriginal = $_FILES['featured_image']['name'];
    $ext = strtolower(pathinfo($imgOriginal, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        flashMessage('error', 'Kun billeder (jpg, png, webp, gif) er tilladt.');
        redirect('/admin/artikler/edit?id=' . $id);
    }
    // Delete old image
    if ($article['featured_image']) {
        $oldImg = PUBLIC_PATH . $article['featured_image'];
        if (file_exists($oldImg)) unlink($oldImg);
    }
    $imgFilename = generateUniqueId('art_') . '.' . $ext;
    $uploadPath = tenantUploadPath('articles');
    move_uploaded_file($_FILES['featured_image']['tmp_name'], $uploadPath . '/' . $imgFilename);
    $data['featured_image'] = '/uploads/' . $tenantId . '/articles/' . $imgFilename;
}

Article::update($id, $data);

logAudit('article_updated', 'article', $id);
flashMessage('success', 'Artikel opdateret.');
redirect('/admin/artikler');
