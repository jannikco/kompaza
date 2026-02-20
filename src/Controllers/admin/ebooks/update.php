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
    'hero_headline' => sanitize($_POST['hero_headline'] ?? '') ?: null,
    'hero_subheadline' => sanitize($_POST['hero_subheadline'] ?? '') ?: null,
    'hero_bg_color' => sanitize($_POST['hero_bg_color'] ?? '') ?: null,
    'hero_cta_text' => sanitize($_POST['hero_cta_text'] ?? '') ?: null,
    'features_headline' => sanitize($_POST['features_headline'] ?? '') ?: null,
    'features' => $_POST['features'] ?? null,
    'key_metrics' => !empty($_POST['key_metrics']) ? $_POST['key_metrics'] : null,
    'chapters' => !empty($_POST['chapters']) ? $_POST['chapters'] : null,
    'target_audience' => !empty($_POST['target_audience']) ? $_POST['target_audience'] : null,
    'testimonials' => !empty($_POST['testimonials']) ? $_POST['testimonials'] : null,
    'faq' => !empty($_POST['faq']) ? $_POST['faq'] : null,
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
        deleteUploadedFile($ebook['pdf_filename']);
    }
    $data['pdf_filename'] = uploadPrivateFile($_FILES['pdf_file']['tmp_name'], 'pdfs', 'ebook', 'pdf');
    $data['pdf_original_name'] = $pdfOriginalName;
}

// Handle cover image replacement
if (!empty($_FILES['cover_image']['name']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        flashMessage('error', 'Kun billeder (jpg, png, webp, gif) er tilladt.');
        redirect('/admin/eboger/edit?id=' . $id);
    }
    // Delete old image
    if ($ebook['cover_image_path']) {
        deleteUploadedFile($ebook['cover_image_path']);
    }
    $data['cover_image_path'] = uploadPublicFile($_FILES['cover_image']['tmp_name'], 'ebooks', 'ebook_cover', $ext);
}

Ebook::update($id, $data);

logAudit('ebook_updated', 'ebook', $id);
flashMessage('success', 'E-bog opdateret.');
redirect('/admin/eboger');
