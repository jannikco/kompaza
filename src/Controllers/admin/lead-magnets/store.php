<?php

use App\Models\LeadMagnet;

if (!isPost()) redirect('/admin/lead-magnets');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/lead-magnets/create');
}

$tenantId = currentTenantId();

$title = sanitize($_POST['title'] ?? '');
$slug = sanitize($_POST['slug'] ?? '') ?: slugify($title);

if (!$title || !$slug) {
    flashMessage('error', 'Titel og slug er påkrævet.');
    redirect('/admin/lead-magnets/create');
}

// Handle PDF upload
$pdfFilename = null;
$pdfOriginalName = null;
if (!empty($_FILES['pdf_file']['name']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
    $pdfOriginalName = $_FILES['pdf_file']['name'];
    $ext = strtolower(pathinfo($pdfOriginalName, PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        flashMessage('error', 'Kun PDF-filer er tilladt.');
        redirect('/admin/lead-magnets/create');
    }
    $pdfFilename = generateUniqueId('lm_') . '.pdf';
    $storagePath = tenantStoragePath();
    move_uploaded_file($_FILES['pdf_file']['tmp_name'], $storagePath . '/' . $pdfFilename);
}

// Handle hero image upload
$heroImagePath = null;
if (!empty($_FILES['hero_image']['name']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
    $imgOriginal = $_FILES['hero_image']['name'];
    $ext = strtolower(pathinfo($imgOriginal, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        flashMessage('error', 'Kun billeder (jpg, png, webp, gif) er tilladt.');
        redirect('/admin/lead-magnets/create');
    }
    $imgFilename = generateUniqueId('lm_hero_') . '.' . $ext;
    $uploadPath = tenantUploadPath('lead-magnets');
    move_uploaded_file($_FILES['hero_image']['tmp_name'], $uploadPath . '/' . $imgFilename);
    $heroImagePath = '/uploads/' . $tenantId . '/lead-magnets/' . $imgFilename;
}

$id = LeadMagnet::create([
    'tenant_id' => $tenantId,
    'slug' => $slug,
    'title' => $title,
    'subtitle' => sanitize($_POST['subtitle'] ?? ''),
    'meta_description' => sanitize($_POST['meta_description'] ?? ''),
    'hero_headline' => sanitize($_POST['hero_headline'] ?? ''),
    'hero_subheadline' => sanitize($_POST['hero_subheadline'] ?? ''),
    'hero_cta_text' => sanitize($_POST['hero_cta_text'] ?? 'Download Free'),
    'hero_bg_color' => sanitize($_POST['hero_bg_color'] ?? '#1e40af'),
    'hero_image_path' => $heroImagePath,
    'features_headline' => sanitize($_POST['features_headline'] ?? ''),
    'features' => $_POST['features'] ?? null,
    'pdf_filename' => $pdfFilename,
    'pdf_original_name' => $pdfOriginalName,
    'email_subject' => sanitize($_POST['email_subject'] ?? ''),
    'email_body_html' => $_POST['email_body_html'] ?? null,
    'brevo_list_id' => sanitize($_POST['brevo_list_id'] ?? ''),
    'status' => sanitize($_POST['status'] ?? 'draft'),
]);

logAudit('lead_magnet_created', 'lead_magnet', $id);
flashMessage('success', 'Lead magnet oprettet.');
redirect('/admin/lead-magnets');
