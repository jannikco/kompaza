<?php

use App\Models\Tenant;

if (!isPost()) {
    redirect('/tenants');
}

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid security token. Please try again.');
    redirect('/tenants/create');
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
    redirect('/tenants/create');
}

if (empty($slug)) {
    flashMessage('error', 'Slug is required.');
    redirect('/tenants/create');
}

if (Tenant::slugExists($slug)) {
    flashMessage('error', 'This slug is already taken. Please choose another.');
    redirect('/tenants/create');
}

// Validate status
$validStatuses = ['trial', 'active', 'suspended', 'cancelled'];
if (!in_array($status, $validStatuses)) {
    $status = 'trial';
}

// Create tenant
$tenantId = Tenant::create([
    'name' => $name,
    'slug' => $slug,
    'email' => $email ?: null,
    'status' => $status,
    'plan_id' => $planId,
    'trial_ends_at' => $trialEndsAt ? $trialEndsAt . ' 23:59:59' : date('Y-m-d H:i:s', strtotime('+7 days')),
]);

if ($tenantId) {
    logAudit('tenant_created', 'tenant', $tenantId, ['name' => $name, 'slug' => $slug]);
    flashMessage('success', 'Tenant "' . $name . '" created successfully.');
} else {
    flashMessage('error', 'Failed to create tenant. Please try again.');
}

redirect('/tenants');
