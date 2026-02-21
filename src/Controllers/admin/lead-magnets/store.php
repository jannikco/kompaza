<?php

use App\Models\LeadMagnet;

if (!isPost()) redirect('/admin/lead-magnets');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/lead-magnets/opret');
}

$tenantId = currentTenantId();

$title = sanitize($_POST['title'] ?? '');
$slug = sanitize($_POST['slug'] ?? '') ?: slugify($title);

if (!$title || !$slug) {
    flashMessage('error', 'Title and slug are required.');
    redirect('/admin/lead-magnets/opret');
}

// Handle PDF upload — check for pre-uploaded (AI step) first
$pdfFilename = null;
$pdfOriginalName = null;
if (!empty($_POST['pdf_filename_existing'])) {
    $pdfFilename = $_POST['pdf_filename_existing'];
    $pdfOriginalName = sanitize($_POST['pdf_original_name_existing'] ?? 'document.pdf');
} elseif (!empty($_FILES['pdf_file']['name']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
    $pdfOriginalName = $_FILES['pdf_file']['name'];
    $ext = strtolower(pathinfo($pdfOriginalName, PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        flashMessage('error', 'Only PDF files are allowed.');
        redirect('/admin/lead-magnets/opret');
    }
    $pdfFilename = uploadPrivateFile($_FILES['pdf_file']['tmp_name'], 'pdfs', 'lm', 'pdf');
}

// Handle hero image upload
$heroImagePath = null;
if (!empty($_FILES['hero_image']['name']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        flashMessage('error', 'Only images (jpg, png, webp, gif) are allowed.');
        redirect('/admin/lead-magnets/opret');
    }
    $heroImagePath = uploadPublicFile($_FILES['hero_image']['tmp_name'], 'lead-magnets', 'lm_hero', $ext);
}

// Handle cover image — check for AI-generated (pre-uploaded) first, then manual upload
$coverImagePath = null;
if (!empty($_POST['cover_image_path_existing'])) {
    $coverImagePath = $_POST['cover_image_path_existing'];
} elseif (!empty($_FILES['cover_image']['name']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        flashMessage('error', 'Only images (jpg, png, webp, gif) are allowed.');
        redirect('/admin/lead-magnets/opret');
    }
    $coverImagePath = uploadPublicFile($_FILES['cover_image']['tmp_name'], 'lead-magnets', 'lm_cover', $ext);
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
    'cover_image_path' => $coverImagePath,
    'features_headline' => sanitize($_POST['features_headline'] ?? ''),
    'features' => $_POST['features'] ?? null,
    'chapters' => $_POST['chapters'] ?? null,
    'key_statistics' => $_POST['key_statistics'] ?? null,
    'target_audience' => $_POST['target_audience'] ?? null,
    'faq' => $_POST['faq'] ?? null,
    'before_after' => $_POST['before_after'] ?? null,
    'author_bio' => sanitize($_POST['author_bio'] ?? ''),
    'testimonial_templates' => $_POST['testimonial_templates'] ?? null,
    'social_proof' => $_POST['social_proof'] ?? null,
    'pdf_filename' => $pdfFilename,
    'pdf_original_name' => $pdfOriginalName,
    'email_subject' => sanitize($_POST['email_subject'] ?? ''),
    'email_body_html' => $_POST['email_body_html'] ?? null,
    'brevo_list_id' => sanitize($_POST['brevo_list_id'] ?? ''),
    'status' => sanitize($_POST['status'] ?? 'draft'),
]);

logAudit('lead_magnet_created', 'lead_magnet', $id);
flashMessage('success', 'Lead magnet created.');
redirect('/admin/lead-magnets');
