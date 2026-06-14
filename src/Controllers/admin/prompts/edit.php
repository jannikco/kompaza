<?php

use App\Models\Prompt;
use App\Models\PromptCategory;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    flashMessage('error', 'Prompt not found.');
    redirect('/admin/prompts');
}

$prompt = Prompt::find($id, $tenantId);

if (!$prompt) {
    flashMessage('error', 'Prompt not found.');
    redirect('/admin/prompts');
}

$categories = PromptCategory::allByTenant($tenantId);

view('admin/prompts/edit', [
    'tenant' => currentTenant(),
    'prompt' => $prompt,
    'categories' => $categories,
]);
