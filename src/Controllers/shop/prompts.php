<?php

use App\Models\Prompt;
use App\Models\PromptCategory;
use App\Models\CustomerMembership;
use App\Services\MembershipGuard;

$tenant = currentTenant();
$tenantId = currentTenantId();

$categories = PromptCategory::allByTenant($tenantId);
$categorySlug = sanitize($_GET['category'] ?? '');
$search = sanitize($_GET['q'] ?? '');

$categoryId = null;
if ($categorySlug) {
    $cat = PromptCategory::findBySlug($categorySlug, $tenantId);
    if ($cat) $categoryId = $cat['id'];
}

if ($search) {
    $prompts = Prompt::search($tenantId, $search);
} else {
    $prompts = Prompt::published($tenantId, $categoryId);
}

$userTierLevel = 0;
if (isAuthenticated()) {
    $userTierLevel = MembershipGuard::getTierLevel(currentUserId(), $tenantId);
}

view('shop/prompts', [
    'tenant' => $tenant,
    'prompts' => $prompts,
    'categories' => $categories,
    'currentCategory' => $categorySlug,
    'search' => $search,
    'userTierLevel' => $userTierLevel,
]);
