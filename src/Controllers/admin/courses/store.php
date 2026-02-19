<?php

use App\Models\Course;

if (!isPost()) redirect('/admin/kurser');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/kurser/opret');
}

$tenantId = currentTenantId();

$title = sanitize($_POST['title'] ?? '');
$slug = sanitize($_POST['slug'] ?? '') ?: slugify($title);

if (!$title) {
    flashMessage('error', 'Course title is required.');
    redirect('/admin/kurser/opret');
}

// Check slug uniqueness
$existing = Course::findBySlugAnyStatus($slug, $tenantId);
if ($existing) {
    $slug = $slug . '-' . uniqid();
}

// Handle cover image upload
$coverImagePath = null;
if (!empty($_FILES['cover_image']['name']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        flashMessage('error', 'Only images (jpg, png, webp, gif) are allowed.');
        redirect('/admin/kurser/opret');
    }
    $imgFilename = generateUniqueId('course_') . '.' . $ext;
    $uploadPath = tenantUploadPath('courses');
    move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadPath . '/' . $imgFilename);
    $coverImagePath = '/uploads/' . $tenantId . '/courses/' . $imgFilename;
}

// Handle instructor image upload
$instructorImagePath = null;
if (!empty($_FILES['instructor_image']['name']) && $_FILES['instructor_image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['instructor_image']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        $imgFilename = generateUniqueId('instr_') . '.' . $ext;
        $uploadPath = tenantUploadPath('courses');
        move_uploaded_file($_FILES['instructor_image']['tmp_name'], $uploadPath . '/' . $imgFilename);
        $instructorImagePath = '/uploads/' . $tenantId . '/courses/' . $imgFilename;
    }
}

$id = Course::create([
    'tenant_id' => $tenantId,
    'slug' => $slug,
    'title' => $title,
    'subtitle' => sanitize($_POST['subtitle'] ?? ''),
    'description' => $_POST['description'] ?? null,
    'short_description' => sanitize($_POST['short_description'] ?? ''),
    'cover_image_path' => $coverImagePath,
    'pricing_type' => sanitize($_POST['pricing_type'] ?? 'free'),
    'price_dkk' => !empty($_POST['price_dkk']) ? (float)$_POST['price_dkk'] : null,
    'compare_price_dkk' => !empty($_POST['compare_price_dkk']) ? (float)$_POST['compare_price_dkk'] : null,
    'subscription_price_monthly_dkk' => !empty($_POST['subscription_price_monthly_dkk']) ? (float)$_POST['subscription_price_monthly_dkk'] : null,
    'subscription_price_yearly_dkk' => !empty($_POST['subscription_price_yearly_dkk']) ? (float)$_POST['subscription_price_yearly_dkk'] : null,
    'stripe_monthly_price_id' => sanitize($_POST['stripe_monthly_price_id'] ?? ''),
    'stripe_yearly_price_id' => sanitize($_POST['stripe_yearly_price_id'] ?? ''),
    'status' => sanitize($_POST['status'] ?? 'draft'),
    'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
    'drip_enabled' => isset($_POST['drip_enabled']) ? 1 : 0,
    'drip_interval_days' => !empty($_POST['drip_interval_days']) ? (int)$_POST['drip_interval_days'] : null,
    'instructor_name' => sanitize($_POST['instructor_name'] ?? ''),
    'instructor_bio' => sanitize($_POST['instructor_bio'] ?? ''),
    'instructor_image_path' => $instructorImagePath,
]);

logAudit('course_created', 'course', $id);
flashMessage('success', 'Course created. Now add modules and lessons.');
redirect('/admin/kurser/rediger?id=' . $id);
