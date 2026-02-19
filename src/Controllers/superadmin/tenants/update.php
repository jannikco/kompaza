<?php

use App\Models\Tenant;

if (!isPost()) {
    redirect('/tenants');
}

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid security token. Please try again.');
    redirect('/tenants');
}

$id = (int)($_POST['id'] ?? 0);

if (!$id) {
    flashMessage('error', 'Tenant not found.');
    redirect('/tenants');
}

$tenant = Tenant::find($id);

if (!$tenant) {
    flashMessage('error', 'Tenant not found.');
    redirect('/tenants');
}

$name = sanitize($_POST['name'] ?? '');
$slug = slugify($_POST['slug'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$status = sanitize($_POST['status'] ?? 'trial');
$planId = !empty($_POST['plan_id']) ? (int)$_POST['plan_id'] : null;
$trialEndsAt = sanitize($_POST['trial_ends_at'] ?? '');

// Validation
if (empty($name)) {
    flashMessage('error', 'Tenant name is required.');
    redirect('/tenants/edit?id=' . $id);
}

if (empty($slug)) {
    flashMessage('error', 'Slug is required.');
    redirect('/tenants/edit?id=' . $id);
}

// Check slug uniqueness (exclude current tenant)
if (Tenant::slugExists($slug, $id)) {
    flashMessage('error', 'This slug is already taken. Please choose another.');
    redirect('/tenants/edit?id=' . $id);
}

// Validate status
$validStatuses = ['trial', 'active', 'suspended', 'cancelled'];
if (!in_array($status, $validStatuses)) {
    $status = 'trial';
}

// Update tenant
$updateData = [
    'name' => $name,
    'slug' => $slug,
    'email' => $email ?: null,
    'status' => $status,
    'plan_id' => $planId,
];

if ($trialEndsAt) {
    $updateData['trial_ends_at'] = $trialEndsAt . ' 23:59:59';
}

$result = Tenant::update($id, $updateData);

if ($result) {
    logAudit('tenant_updated', 'tenant', $id, ['name' => $name, 'slug' => $slug, 'status' => $status]);
    flashMessage('success', 'Tenant "' . $name . '" updated successfully.');
} else {
    flashMessage('error', 'Failed to update tenant. Please try again.');
}

redirect('/tenants');
