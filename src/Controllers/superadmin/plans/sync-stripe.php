<?php

use App\Models\SubscriptionPlan;
use App\Services\StripeService;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/tenants/plans');
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    flashMessage('error', 'Ugyldig anmodning.');
    redirect('/tenants/plans');
}

$plans = SubscriptionPlan::all();
$synced = 0;

try {
    foreach ($plans as $plan) {
        // Create Stripe Product if not exists
        $productId = $plan['stripe_product_id'];
        if (!$productId) {
            $product = StripeService::createProduct($plan['name'], ['plan_slug' => $plan['slug']]);
            $productId = $product->id;
            SubscriptionPlan::update($plan['id'], ['stripe_product_id' => $productId]);
        }

        // Create monthly price if not exists
        if (!$plan['stripe_price_monthly_id'] && $plan['price_monthly_usd'] > 0) {
            $price = StripeService::createPrice($productId, $plan['price_monthly_usd'], 'monthly');
            SubscriptionPlan::update($plan['id'], ['stripe_price_monthly_id' => $price->id]);
        }

        // Create annual price if not exists
        if (!$plan['stripe_price_annual_id'] && $plan['price_annual_usd'] > 0) {
            $annualTotal = $plan['price_annual_usd'] * 12;
            $price = StripeService::createPrice($productId, $annualTotal, 'annual');
            SubscriptionPlan::update($plan['id'], ['stripe_price_annual_id' => $price->id]);
        }

        $synced++;
    }

    flashMessage('success', "$synced planer synkroniseret med Stripe.");
} catch (\Exception $e) {
    error_log("Stripe sync error: " . $e->getMessage());
    flashMessage('error', 'Fejl ved synkronisering: ' . $e->getMessage());
}

redirect('/tenants/plans');
