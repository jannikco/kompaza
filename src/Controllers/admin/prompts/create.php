<?php

use App\Models\PromptCategory;

$tenantId = currentTenantId();
$categories = PromptCategory::allByTenant($tenantId);

view('admin/prompts/create', [
    'tenant' => currentTenant(),
    'categories' => $categories,
]);
