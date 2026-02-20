<?php

use App\Models\Plan;

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/plans/create');
}

$name = sanitize($_POST['name'] ?? '');
$slug = sanitize($_POST['slug'] ?? '') ?: slugify($name);
$priceMonthly = (float)($_POST['price_monthly_usd'] ?? 0);
$priceYearly = !empty($_POST['price_yearly_usd']) ? (float)$_POST['price_yearly_usd'] : null;

if (!$name || !$slug) {
    flashMessage('error', 'Name and slug are required.');
    redirect('/plans/create');
}

Plan::create([
    'name' => $name,
    'slug' => $slug,
    'price_monthly_usd' => $priceMonthly,
    'price_yearly_usd' => $priceYearly,
    'max_customers' => !empty($_POST['max_customers']) ? (int)$_POST['max_customers'] : null,
    'max_leads' => !empty($_POST['max_leads']) ? (int)$_POST['max_leads'] : null,
    'max_campaigns' => !empty($_POST['max_campaigns']) ? (int)$_POST['max_campaigns'] : null,
    'max_products' => !empty($_POST['max_products']) ? (int)$_POST['max_products'] : null,
    'max_lead_magnets' => !empty($_POST['max_lead_magnets']) ? (int)$_POST['max_lead_magnets'] : null,
    'is_active' => isset($_POST['is_active']) ? 1 : 0,
    'sort_order' => (int)($_POST['sort_order'] ?? 0),
]);

logAudit('plan_created', 'plan', null, ['name' => $name]);
flashMessage('success', 'Plan created successfully.');
redirect('/plans');
