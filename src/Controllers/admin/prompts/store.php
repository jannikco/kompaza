<?php

use App\Models\Prompt;

if (!isPost()) redirect('/admin/prompts');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/prompts/create');
}

$tenantId = currentTenantId();

$title = sanitize($_POST['title'] ?? '');
$categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
$promptText = $_POST['prompt_text'] ?? '';
$description = sanitize($_POST['description'] ?? '');
$useCase = sanitize($_POST['use_case'] ?? '');
$aiTool = sanitize($_POST['ai_tool'] ?? '');
$membershipTierLevel = (int)($_POST['membership_tier_level'] ?? 0);
$isFeatured = isset($_POST['is_featured']) ? 1 : 0;
$status = sanitize($_POST['status'] ?? 'draft');
$sortOrder = (int)($_POST['sort_order'] ?? 0);

// Tags: comma-separated string to JSON array
$tagsRaw = $_POST['tags'] ?? '';
$tags = null;
if (!empty(trim($tagsRaw))) {
    $tagsArray = array_map('trim', explode(',', $tagsRaw));
    $tagsArray = array_filter($tagsArray, fn($t) => $t !== '');
    $tags = json_encode(array_values($tagsArray));
}

if (!$title) {
    flashMessage('error', 'Title is required.');
    redirect('/admin/prompts/create');
}

if (!trim($promptText)) {
    flashMessage('error', 'Prompt text is required.');
    redirect('/admin/prompts/create');
}

$id = Prompt::create([
    'tenant_id' => $tenantId,
    'category_id' => $categoryId,
    'title' => $title,
    'slug' => slugify($title),
    'prompt_text' => $promptText,
    'description' => $description ?: null,
    'use_case' => $useCase ?: null,
    'ai_tool' => $aiTool ?: null,
    'tags' => $tags,
    'membership_tier_level' => $membershipTierLevel,
    'is_featured' => $isFeatured,
    'status' => $status,
    'sort_order' => $sortOrder,
]);

logAudit('prompt_created', 'prompt', $id);
flashMessage('success', 'Prompt created.');
redirect('/admin/prompts');
