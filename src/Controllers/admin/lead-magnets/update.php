<?php

use App\Models\LeadMagnet;

if (!isPost()) redirect('/admin/lead-magnets');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/lead-magnets');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/lead-magnets');

$leadMagnet = LeadMagnet::find($id, $tenantId);
if (!$leadMagnet) {
    flashMessage('error', 'Lead magnet ikke fundet.');
    redirect('/admin/lead-magnets');
}

$data = [
    'title' => sanitize($_POST['title'] ?? ''),
    'slug' => sanitize($_POST['slug'] ?? '') ?: slugify($_POST['title'] ?? ''),
    'subtitle' => sanitize($_POST['subtitle'] ?? ''),
    'meta_description' => sanitize($_POST['meta_description'] ?? ''),
    'hero_headline' => sanitize($_POST['hero_headline'] ?? ''),
    'hero_subheadline' => sanitize($_POST['hero_subheadline'] ?? ''),
    'hero_cta_text' => sanitize($_POST['hero_cta_text'] ?? 'Download Free'),
    'hero_bg_color' => sanitize($_POST['hero_bg_color'] ?? '#1e40af'),
    'features_headline' => sanitize($_POST['features_headline'] ?? ''),
    'features' => $_POST['features'] ?? null,
    'email_subject' => sanitize($_POST['email_subject'] ?? ''),
    'email_body_html' => $_POST['email_body_html'] ?? null,
    'brevo_list_id' => sanitize($_POST['brevo_list_id'] ?? ''),
    'status' => sanitize($_POST['status'] ?? 'draft'),
];

// Handle PDF replacement
if (!empty($_FILES['pdf_file']['name']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
    $pdfOriginalName = $_FILES['pdf_file']['name'];
    $ext = strtolower(pathinfo($pdfOriginalName, PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        flashMessage('error', 'Kun PDF-filer er tilladt.');
        redirect('/admin/lead-magnets/edit?id=' . $id);
    }
    // Delete old PDF
    if ($leadMagnet['pdf_filename']) {
        $oldPath = tenantStoragePath() . '/' . $leadMagnet['pdf_filename'];
        if (file_exists($oldPath)) unlink($oldPath);
    }
    $pdfFilename = generateUniqueId('lm_') . '.pdf';
    move_uploaded_file($_FILES['pdf_file']['tmp_name'], tenantStoragePath() . '/' . $pdfFilename);
    $data['pdf_filename'] = $pdfFilename;
    $data['pdf_original_name'] = $pdfOriginalName;
}

// Handle hero image replacement
if (!empty($_FILES['hero_image']['name']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
    $imgOriginal = $_FILES['hero_image']['name'];
    $ext = strtolower(pathinfo($imgOriginal, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        flashMessage('error', 'Kun billeder (jpg, png, webp, gif) er tilladt.');
        redirect('/admin/lead-magnets/edit?id=' . $id);
    }
    // Delete old image
    if ($leadMagnet['hero_image_path']) {
        $oldImg = PUBLIC_PATH . $leadMagnet['hero_image_path'];
        if (file_exists($oldImg)) unlink($oldImg);
    }
    $imgFilename = generateUniqueId('lm_hero_') . '.' . $ext;
    $uploadPath = tenantUploadPath('lead-magnets');
    move_uploaded_file($_FILES['hero_image']['tmp_name'], $uploadPath . '/' . $imgFilename);
    $data['hero_image_path'] = '/uploads/' . $tenantId . '/lead-magnets/' . $imgFilename;
}

LeadMagnet::update($id, $data);

logAudit('lead_magnet_updated', 'lead_magnet', $id);
flashMessage('success', 'Lead magnet opdateret.');
redirect('/admin/lead-magnets');
