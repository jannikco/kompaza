<?php

use App\Models\Course;

if (!isPost()) redirect('/admin/kurser');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/kurser');
}

$tenantId = currentTenantId();
$courseId = (int)($_POST['id'] ?? 0);

$course = Course::find($courseId, $tenantId);
if (!$course) {
    flashMessage('error', 'Course not found.');
    redirect('/admin/kurser');
}

$title = sanitize($_POST['title'] ?? '');
if (!$title) {
    flashMessage('error', 'Course title is required.');
    redirect('/admin/kurser/rediger?id=' . $courseId);
}

$slug = sanitize($_POST['slug'] ?? '') ?: slugify($title);

// Check slug uniqueness (exclude current)
$existing = Course::findBySlugAnyStatus($slug, $tenantId);
if ($existing && $existing['id'] != $courseId) {
    $slug = $slug . '-' . uniqid();
}

$data = [
    'slug' => $slug,
    'title' => $title,
    'subtitle' => sanitize($_POST['subtitle'] ?? ''),
    'description' => $_POST['description'] ?? null,
    'short_description' => sanitize($_POST['short_description'] ?? ''),
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
];

// Handle cover image upload
if (!empty($_FILES['cover_image']['name']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        $imgFilename = generateUniqueId('course_') . '.' . $ext;
        $uploadPath = tenantUploadPath('courses');
        move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadPath . '/' . $imgFilename);
        $data['cover_image_path'] = '/uploads/' . $tenantId . '/courses/' . $imgFilename;
    }
}

// Handle instructor image upload
if (!empty($_FILES['instructor_image']['name']) && $_FILES['instructor_image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['instructor_image']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        $imgFilename = generateUniqueId('instr_') . '.' . $ext;
        $uploadPath = tenantUploadPath('courses');
        move_uploaded_file($_FILES['instructor_image']['tmp_name'], $uploadPath . '/' . $imgFilename);
        $data['instructor_image_path'] = '/uploads/' . $tenantId . '/courses/' . $imgFilename;
    }
}

Course::update($courseId, $data);
logAudit('course_updated', 'course', $courseId);
flashMessage('success', 'Course updated.');
redirect('/admin/kurser/rediger?id=' . $courseId);
