<?php

use App\Models\MastermindProgram;

if (!isPost()) redirect('/admin/mastermind');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/mastermind');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/mastermind');

$program = MastermindProgram::find($id, $tenantId);
if (!$program) {
    flashMessage('error', 'Program not found.');
    redirect('/admin/mastermind');
}

$title = sanitize($_POST['title'] ?? '');
$slug = slugify($_POST['slug'] ?? $title);
$description = $_POST['description'] ?? '';
$shortDescription = sanitize($_POST['short_description'] ?? '');
$status = sanitize($_POST['status'] ?? 'draft');

if (empty($title)) {
    flashMessage('error', 'Title is required.');
    redirect('/admin/mastermind/edit?id=' . $id);
}

$updateData = [
    'title' => $title,
    'slug' => $slug,
    'description' => $description,
    'short_description' => $shortDescription,
    'status' => $status,
];

// Handle cover image upload
if (!empty($_FILES['cover_image']['tmp_name']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $fileType = mime_content_type($_FILES['cover_image']['tmp_name']);
    if (in_array($fileType, $allowedTypes)) {
        // Delete old image
        if (!empty($program['cover_image_path'])) {
            deleteUploadedFile($program['cover_image_path']);
        }
        $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $updateData['cover_image_path'] = uploadPublicFile($_FILES['cover_image']['tmp_name'], 'mastermind', 'mm', $ext);
    }
}

MastermindProgram::update($id, $updateData);

logAudit('mastermind_updated', 'mastermind_program', $id);
flashMessage('success', 'Mastermind program updated successfully.');
redirect('/admin/mastermind/edit?id=' . $id);
