<?php

use App\Models\MembershipPlan;

if (!isPost()) redirect('/admin/memberships');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/memberships');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/memberships');

$plan = MembershipPlan::find($id, $tenantId);
if (!$plan) {
    flashMessage('error', 'Membership plan not found.');
    redirect('/admin/memberships');
}

$name = sanitize($_POST['name'] ?? '');
$slug = sanitize($_POST['slug'] ?? '') ?: slugify($name);

if (!$name) {
    flashMessage('error', 'Plan name is required.');
    redirect('/admin/memberships/edit?id=' . $id);
}

$isDefault = isset($_POST['is_default']) ? 1 : 0;

if ($isDefault) {
    MembershipPlan::clearDefault($tenantId);
}

$data = [
    'name' => $name,
    'slug' => $slug,
    'tier_level' => (int)($_POST['tier_level'] ?? 0),
    'description' => $_POST['description'] ?? null,
    'price_monthly' => !empty($_POST['price_monthly']) ? (float)$_POST['price_monthly'] : null,
    'price_yearly' => !empty($_POST['price_yearly']) ? (float)$_POST['price_yearly'] : null,
    'stripe_monthly_price_id' => !empty($_POST['stripe_monthly_price_id']) ? sanitize($_POST['stripe_monthly_price_id']) : null,
    'stripe_yearly_price_id' => !empty($_POST['stripe_yearly_price_id']) ? sanitize($_POST['stripe_yearly_price_id']) : null,
    'max_courses' => !empty($_POST['max_courses']) ? (int)$_POST['max_courses'] : null,
    'max_ebooks' => !empty($_POST['max_ebooks']) ? (int)$_POST['max_ebooks'] : null,
    'can_access_prompts' => isset($_POST['can_access_prompts']) ? 1 : 0,
    'can_post_community' => isset($_POST['can_post_community']) ? 1 : 0,
    'can_access_live_qa' => isset($_POST['can_access_live_qa']) ? 1 : 0,
    'community_read_only' => isset($_POST['community_read_only']) ? 1 : 0,
    'discount_percent' => (float)($_POST['discount_percent'] ?? 0),
    'is_default' => $isDefault,
    'status' => sanitize($_POST['status'] ?? 'active'),
    'sort_order' => (int)($_POST['sort_order'] ?? 0),
];

MembershipPlan::update($id, $data);

logAudit('membership_plan_updated', 'membership_plan', $id);
flashMessage('success', 'Membership plan updated.');
redirect('/admin/memberships');
