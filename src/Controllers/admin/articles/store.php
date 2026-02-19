<?php

use App\Models\Article;

if (!isPost()) redirect('/admin/artikler');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/artikler/create');
}

$tenantId = currentTenantId();

$title = sanitize($_POST['title'] ?? '');
$slug = sanitize($_POST['slug'] ?? '') ?: slugify($title);
$status = sanitize($_POST['status'] ?? 'draft');

if (!$title) {
    flashMessage('error', 'Titel er påkrævet.');
    redirect('/admin/artikler/create');
}

// Handle featured image upload
$featuredImage = null;
if (!empty($_FILES['featured_image']['name']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
    $imgOriginal = $_FILES['featured_image']['name'];
    $ext = strtolower(pathinfo($imgOriginal, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        flashMessage('error', 'Kun billeder (jpg, png, webp, gif) er tilladt.');
        redirect('/admin/artikler/create');
    }
    $imgFilename = generateUniqueId('art_') . '.' . $ext;
    $uploadPath = tenantUploadPath('articles');
    move_uploaded_file($_FILES['featured_image']['tmp_name'], $uploadPath . '/' . $imgFilename);
    $featuredImage = '/uploads/' . $tenantId . '/articles/' . $imgFilename;
}

// Set published_at if publishing and not explicitly set
$publishedAt = $_POST['published_at'] ?? null;
if ($status === 'published' && !$publishedAt) {
    $publishedAt = date('Y-m-d H:i:s');
}

$id = Article::create([
    'tenant_id' => $tenantId,
    'slug' => $slug,
    'title' => $title,
    'excerpt' => sanitize($_POST['excerpt'] ?? ''),
    'content' => $_POST['content'] ?? null,
    'featured_image' => $featuredImage,
    'meta_description' => sanitize($_POST['meta_description'] ?? ''),
    'category' => sanitize($_POST['category'] ?? ''),
    'tags' => !empty($_POST['tags']) ? sanitize($_POST['tags']) : null,
    'status' => $status,
    'published_at' => $publishedAt,
    'author_name' => sanitize($_POST['author_name'] ?? ''),
]);

logAudit('article_created', 'article', $id);
flashMessage('success', 'Artikel oprettet.');
redirect('/admin/artikler');
