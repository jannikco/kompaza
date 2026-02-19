<?php

use App\Models\Ebook;

if (!isPost()) redirect('/admin/eboger');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/eboger');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/eboger');

$ebook = Ebook::find($id, $tenantId);
if (!$ebook) {
    flashMessage('error', 'E-bog ikke fundet.');
    redirect('/admin/eboger');
}

$data = [
    'title' => sanitize($_POST['title'] ?? ''),
    'slug' => sanitize($_POST['slug'] ?? '') ?: slugify($_POST['title'] ?? ''),
    'subtitle' => sanitize($_POST['subtitle'] ?? ''),
    'description' => $_POST['description'] ?? null,
    'hero_headline' => sanitize($_POST['hero_headline'] ?? ''),
    'hero_subheadline' => sanitize($_POST['hero_subheadline'] ?? ''),
    'features' => $_POST['features'] ?? null,
    'page_count' => (int)($_POST['page_count'] ?? 0) ?: null,
    'price_dkk' => (float)($_POST['price_dkk'] ?? 0),
    'meta_description' => sanitize($_POST['meta_description'] ?? ''),
    'status' => sanitize($_POST['status'] ?? 'draft'),
];

// Handle PDF replacement
if (!empty($_FILES['pdf_file']['name']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
    $pdfOriginalName = $_FILES['pdf_file']['name'];
    $ext = strtolower(pathinfo($pdfOriginalName, PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        flashMessage('error', 'Kun PDF-filer er tilladt.');
        redirect('/admin/eboger/edit?id=' . $id);
    }
    // Delete old PDF
    if ($ebook['pdf_filename']) {
        $oldPath = tenantStoragePath() . '/' . $ebook['pdf_filename'];
        if (file_exists($oldPath)) unlink($oldPath);
    }
    $pdfFilename = generateUniqueId('ebook_') . '.pdf';
    move_uploaded_file($_FILES['pdf_file']['tmp_name'], tenantStoragePath() . '/' . $pdfFilename);
    $data['pdf_filename'] = $pdfFilename;
    $data['pdf_original_name'] = $pdfOriginalName;
}

// Handle cover image replacement
if (!empty($_FILES['cover_image']['name']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $imgOriginal = $_FILES['cover_image']['name'];
    $ext = strtolower(pathinfo($imgOriginal, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        flashMessage('error', 'Kun billeder (jpg, png, webp, gif) er tilladt.');
        redirect('/admin/eboger/edit?id=' . $id);
    }
    // Delete old image
    if ($ebook['cover_image_path']) {
        $oldImg = PUBLIC_PATH . $ebook['cover_image_path'];
        if (file_exists($oldImg)) unlink($oldImg);
    }
    $imgFilename = generateUniqueId('ebook_cover_') . '.' . $ext;
    $uploadPath = tenantUploadPath('ebooks');
    move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadPath . '/' . $imgFilename);
    $data['cover_image_path'] = '/uploads/' . $tenantId . '/ebooks/' . $imgFilename;
}

Ebook::update($id, $data);

logAudit('ebook_updated', 'ebook', $id);
flashMessage('success', 'E-bog opdateret.');
redirect('/admin/eboger');
