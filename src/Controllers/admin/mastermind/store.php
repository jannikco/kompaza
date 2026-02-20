<?php

use App\Models\MastermindProgram;

if (!isPost()) redirect('/admin/mastermind');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/mastermind/create');
}

$tenantId = currentTenantId();

$title = sanitize($_POST['title'] ?? '');
$slug = slugify($_POST['slug'] ?? $title);
$description = $_POST['description'] ?? '';
$shortDescription = sanitize($_POST['short_description'] ?? '');
$status = sanitize($_POST['status'] ?? 'draft');

if (empty($title)) {
    flashMessage('error', 'Title is required.');
    redirect('/admin/mastermind/create');
}

// Handle cover image upload
$coverImagePath = null;
if (!empty($_FILES['cover_image']['tmp_name']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $fileType = mime_content_type($_FILES['cover_image']['tmp_name']);
    if (in_array($fileType, $allowedTypes)) {
        $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $coverImagePath = uploadPublicFile($_FILES['cover_image']['tmp_name'], 'mastermind', 'mm', $ext);
    }
}

$programId = MastermindProgram::create([
    'tenant_id' => $tenantId,
    'title' => $title,
    'slug' => $slug,
    'description' => $description,
    'short_description' => $shortDescription,
    'cover_image_path' => $coverImagePath,
    'status' => $status,
]);

logAudit('mastermind_created', 'mastermind_program', $programId);
flashMessage('success', 'Mastermind program created successfully.');
redirect('/admin/mastermind');
