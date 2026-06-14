<?php

use App\Models\Funnel;
use App\Models\FunnelStep;

$tenantId = currentTenantId();
$id = (int)($_POST['id'] ?? 0);

$funnel = Funnel::find($id, $tenantId);
if (!$funnel) {
    flashMessage('error', 'Funnel not found.');
    redirect('/admin/funnels');
}

$name = trim($_POST['name'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$funnelType = $_POST['funnel_type'] ?? 'sales';
$description = trim($_POST['description'] ?? '');
$status = $_POST['status'] ?? 'draft';

if (!$name || !$slug) {
    flashMessage('error', 'Name and slug are required.');
    redirect('/admin/funnels/edit?id=' . $id);
}

// Check slug uniqueness (excluding current)
$existing = Funnel::findBySlug($slug, $tenantId);
if ($existing && (int)$existing['id'] !== $id) {
    flashMessage('error', 'A funnel with this slug already exists.');
    redirect('/admin/funnels/edit?id=' . $id);
}

Funnel::update($id, [
    'name' => $name,
    'slug' => $slug,
    'funnel_type' => $funnelType,
    'description' => $description,
    'status' => $status,
]);

// Rebuild steps
FunnelStep::deleteByFunnel($id);

$stepNames = $_POST['step_name'] ?? [];
$stepTypes = $_POST['step_type'] ?? [];
$stepResourceTypes = $_POST['step_resource_type'] ?? [];
$stepResourceIds = $_POST['step_resource_id'] ?? [];
$stepUrls = $_POST['step_custom_url'] ?? [];

foreach ($stepNames as $i => $stepName) {
    if (empty(trim($stepName))) continue;
    FunnelStep::create([
        'funnel_id' => $id,
        'name' => trim($stepName),
        'step_type' => $stepTypes[$i] ?? 'landing_page',
        'sort_order' => $i,
        'resource_type' => !empty($stepResourceTypes[$i]) ? $stepResourceTypes[$i] : null,
        'resource_id' => !empty($stepResourceIds[$i]) ? (int)$stepResourceIds[$i] : null,
        'custom_url' => !empty($stepUrls[$i]) ? trim($stepUrls[$i]) : null,
    ]);
}

flashMessage('success', 'Funnel updated successfully.');
redirect('/admin/funnels/edit?id=' . $id);
