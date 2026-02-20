<?php

use App\Models\Ebook;

if (!isPost()) redirect('/admin/eboger');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/eboger/create');
}

$tenantId = currentTenantId();

$title = sanitize($_POST['title'] ?? '');
$slug = sanitize($_POST['slug'] ?? '') ?: slugify($title);

if (!$title) {
    flashMessage('error', 'Titel er påkrævet.');
    redirect('/admin/eboger/create');
}

// Handle PDF upload
$pdfFilename = null;
$pdfOriginalName = null;
if (!empty($_FILES['pdf_file']['name']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
    $pdfOriginalName = $_FILES['pdf_file']['name'];
    $ext = strtolower(pathinfo($pdfOriginalName, PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        flashMessage('error', 'Kun PDF-filer er tilladt.');
        redirect('/admin/eboger/create');
    }
    $pdfFilename = uploadPrivateFile($_FILES['pdf_file']['tmp_name'], 'pdfs', 'ebook', 'pdf');
}

// Handle cover image upload
$coverImagePath = null;
if (!empty($_FILES['cover_image']['name']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        flashMessage('error', 'Kun billeder (jpg, png, webp, gif) er tilladt.');
        redirect('/admin/eboger/create');
    }
    $coverImagePath = uploadPublicFile($_FILES['cover_image']['tmp_name'], 'ebooks', 'ebook_cover', $ext);
}

$id = Ebook::create([
    'tenant_id' => $tenantId,
    'slug' => $slug,
    'title' => $title,
    'subtitle' => sanitize($_POST['subtitle'] ?? ''),
    'description' => $_POST['description'] ?? null,
    'hero_headline' => sanitize($_POST['hero_headline'] ?? ''),
    'hero_subheadline' => sanitize($_POST['hero_subheadline'] ?? ''),
    'cover_image_path' => $coverImagePath,
    'features' => $_POST['features'] ?? null,
    'pdf_filename' => $pdfFilename,
    'pdf_original_name' => $pdfOriginalName,
    'page_count' => (int)($_POST['page_count'] ?? 0) ?: null,
    'price_dkk' => (float)($_POST['price_dkk'] ?? 0),
    'meta_description' => sanitize($_POST['meta_description'] ?? ''),
    'status' => sanitize($_POST['status'] ?? 'draft'),
]);

logAudit('ebook_created', 'ebook', $id);
flashMessage('success', 'E-bog oprettet.');
redirect('/admin/eboger');
