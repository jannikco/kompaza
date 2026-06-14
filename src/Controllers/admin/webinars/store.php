<?php

use App\Models\Webinar;

$tenantId = currentTenantId();

$title = trim($_POST['title'] ?? '');
$slug = trim($_POST['slug'] ?? '');

if (!$title || !$slug) {
    flashMessage('error', 'Title and slug are required.');
    redirect('/admin/webinars/create');
}

$slug = strtolower(preg_replace('/[^a-z0-9\-]+/', '-', $slug));

$existing = Webinar::findBySlug($slug, $tenantId);
if ($existing) {
    flashMessage('error', 'A webinar with this slug already exists.');
    redirect('/admin/webinars/create');
}

// Handle image upload
$hostImagePath = null;
$registrationImagePath = null;
if (!empty($_FILES['host_image']['tmp_name'])) {
    $uploadDir = PUBLIC_PATH . '/uploads/' . $tenantId . '/webinars/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $ext = pathinfo($_FILES['host_image']['name'], PATHINFO_EXTENSION);
    $filename = 'host-' . $slug . '-' . time() . '.' . $ext;
    move_uploaded_file($_FILES['host_image']['tmp_name'], $uploadDir . $filename);
    $hostImagePath = '/uploads/' . $tenantId . '/webinars/' . $filename;
}
if (!empty($_FILES['registration_image']['tmp_name'])) {
    $uploadDir = PUBLIC_PATH . '/uploads/' . $tenantId . '/webinars/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $ext = pathinfo($_FILES['registration_image']['name'], PATHINFO_EXTENSION);
    $filename = 'reg-' . $slug . '-' . time() . '.' . $ext;
    move_uploaded_file($_FILES['registration_image']['tmp_name'], $uploadDir . $filename);
    $registrationImagePath = '/uploads/' . $tenantId . '/webinars/' . $filename;
}

$bulletPoints = array_filter(array_map('trim', $_POST['bullet_points'] ?? []));

$webinarId = Webinar::create([
    'tenant_id' => $tenantId,
    'title' => $title,
    'slug' => $slug,
    'description' => trim($_POST['description'] ?? '') ?: null,
    'host_name' => trim($_POST['host_name'] ?? '') ?: null,
    'host_bio' => trim($_POST['host_bio'] ?? '') ?: null,
    'host_image_path' => $hostImagePath,
    'webinar_type' => $_POST['webinar_type'] ?? 'live',
    'scheduled_at' => !empty($_POST['scheduled_at']) ? $_POST['scheduled_at'] : null,
    'duration_minutes' => (int)($_POST['duration_minutes'] ?? 60),
    'timezone' => $_POST['timezone'] ?? 'Europe/Copenhagen',
    'embed_url' => trim($_POST['embed_url'] ?? '') ?: null,
    'replay_url' => trim($_POST['replay_url'] ?? '') ?: null,
    'registration_headline' => trim($_POST['registration_headline'] ?? '') ?: null,
    'registration_subheadline' => trim($_POST['registration_subheadline'] ?? '') ?: null,
    'registration_cta_text' => trim($_POST['registration_cta_text'] ?? '') ?: 'Register Now',
    'registration_image_path' => $registrationImagePath,
    'bullet_points' => !empty($bulletPoints) ? json_encode(array_values($bulletPoints)) : null,
    'offer_product_id' => !empty($_POST['offer_product_id']) ? (int)$_POST['offer_product_id'] : null,
    'offer_headline' => trim($_POST['offer_headline'] ?? '') ?: null,
    'offer_description' => trim($_POST['offer_description'] ?? '') ?: null,
    'reminder_sequence_id' => !empty($_POST['reminder_sequence_id']) ? (int)$_POST['reminder_sequence_id'] : null,
    'followup_sequence_id' => !empty($_POST['followup_sequence_id']) ? (int)$_POST['followup_sequence_id'] : null,
    'status' => $_POST['status'] ?? 'draft',
]);

flashMessage('success', 'Webinar created successfully.');
redirect('/admin/webinars/edit?id=' . $webinarId);
