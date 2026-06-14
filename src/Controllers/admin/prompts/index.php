<?php

use App\Models\Prompt;
use App\Models\PromptCategory;

$tenantId = currentTenantId();
$prompts = Prompt::allByTenant($tenantId);
$categories = PromptCategory::allByTenant($tenantId);

view('admin/prompts/index', [
    'tenant' => currentTenant(),
    'prompts' => $prompts,
    'categories' => $categories,
]);
