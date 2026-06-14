<?php

use App\Models\MembershipPlan;

if (!isPost()) redirect('/admin/memberships');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/memberships');
}

$tenantId = currentTenantId();

$name = sanitize($_POST['name'] ?? '');
$slug = sanitize($_POST['slug'] ?? '') ?: slugify($name);
$tierLevel = (int)($_POST['tier_level'] ?? 0);
$description = $_POST['description'] ?? null;
$priceMonthly = !empty($_POST['price_monthly']) ? (float)$_POST['price_monthly'] : null;
$priceYearly = !empty($_POST['price_yearly']) ? (float)$_POST['price_yearly'] : null;
$stripeMonthlyPriceId = sanitize($_POST['stripe_monthly_price_id'] ?? '');
$stripeYearlyPriceId = sanitize($_POST['stripe_yearly_price_id'] ?? '');
$maxCourses = !empty($_POST['max_courses']) ? (int)$_POST['max_courses'] : null;
$maxEbooks = !empty($_POST['max_ebooks']) ? (int)$_POST['max_ebooks'] : null;
$canAccessPrompts = isset($_POST['can_access_prompts']) ? 1 : 0;
$canPostCommunity = isset($_POST['can_post_community']) ? 1 : 0;
$canAccessLiveQa = isset($_POST['can_access_live_qa']) ? 1 : 0;
$communityReadOnly = isset($_POST['community_read_only']) ? 1 : 0;
$discountPercent = (float)($_POST['discount_percent'] ?? 0);
$isDefault = isset($_POST['is_default']) ? 1 : 0;
$sortOrder = (int)($_POST['sort_order'] ?? 0);

if (!$name) {
    flashMessage('error', 'Plan name is required.');
    redirect('/admin/memberships/create');
}

if ($isDefault) {
    MembershipPlan::clearDefault($tenantId);
}

$id = MembershipPlan::create([
    'tenant_id' => $tenantId,
    'name' => $name,
    'slug' => $slug,
    'tier_level' => $tierLevel,
    'description' => $description,
    'price_monthly' => $priceMonthly,
    'price_yearly' => $priceYearly,
    'stripe_monthly_price_id' => $stripeMonthlyPriceId ?: null,
    'stripe_yearly_price_id' => $stripeYearlyPriceId ?: null,
    'max_courses' => $maxCourses,
    'max_ebooks' => $maxEbooks,
    'can_access_prompts' => $canAccessPrompts,
    'can_post_community' => $canPostCommunity,
    'can_access_live_qa' => $canAccessLiveQa,
    'community_read_only' => $communityReadOnly,
    'discount_percent' => $discountPercent,
    'is_default' => $isDefault,
    'sort_order' => $sortOrder,
]);

logAudit('membership_plan_created', 'membership_plan', $id);
flashMessage('success', 'Membership plan created.');
redirect('/admin/memberships');
