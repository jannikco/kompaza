<?php

use App\Auth\Auth;
use App\Models\MembershipPlan;
use App\Models\CustomerMembership;
use App\Services\StripeService;

if (!tenantFeature('memberships')) {
    http_response_code(404);
    view('errors/404');
    exit;
}

Auth::requireCustomer();

if (!isPost()) redirect('/membership');

header('Content-Type: application/json');

$tenantId = currentTenantId();
$userId = currentUserId();
$user = currentUser();

$planId = (int)($_POST['plan_id'] ?? 0);
$billingInterval = sanitize($_POST['billing_interval'] ?? 'monthly');

$plan = MembershipPlan::find($planId, $tenantId);
if (!$plan) {
    echo json_encode(['error' => 'Plan not found']);
    exit;
}

// Check if already subscribed
$existing = CustomerMembership::findActiveByUser($userId, $tenantId);
if ($existing) {
    echo json_encode(['error' => 'You already have an active membership. Please manage it from your dashboard.']);
    exit;
}

$priceId = $billingInterval === 'yearly' ? $plan['stripe_yearly_price_id'] : $plan['stripe_monthly_price_id'];
if (!$priceId) {
    echo json_encode(['error' => 'This plan is not available for ' . $billingInterval . ' billing.']);
    exit;
}

try {
    $stripe = new StripeService(null, $tenantId);

    // Get or create Stripe customer
    $stripeCustomerId = $user['stripe_customer_id'] ?? null;
    if (!$stripeCustomerId) {
        $customer = $stripe->createCustomer($user['email'], $user['name'] ?? '', [
            'kompaza_user_id' => $userId,
            'tenant_id' => $tenantId,
        ]);
        $stripeCustomerId = $customer['id'];
        // Save stripe_customer_id to user
        $db = \App\Database\Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET stripe_customer_id = ? WHERE id = ?");
        $stmt->execute([$stripeCustomerId, $userId]);
    }

    $successUrl = url('/membership/success') . '?session_id={CHECKOUT_SESSION_ID}';
    $cancelUrl = url('/membership');

    $session = $stripe->createMembershipCheckoutSession(
        $stripeCustomerId,
        $priceId,
        [
            'type' => 'membership',
            'plan_id' => $planId,
            'user_id' => $userId,
            'tenant_id' => $tenantId,
            'billing_interval' => $billingInterval,
        ],
        $successUrl,
        $cancelUrl
    );

    echo json_encode(['url' => $session['url']]);
} catch (\Exception $e) {
    echo json_encode(['error' => 'Payment setup failed. Please try again.']);
}
exit;
