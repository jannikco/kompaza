<?php

use App\Auth\Auth;
use App\Models\CustomerMembership;
use App\Services\StripeService;

if (!tenantFeature('memberships')) {
    http_response_code(404);
    view('errors/404');
    exit;
}

Auth::requireCustomer();

if (!isPost()) redirect('/membership/dashboard');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request. Please try again.');
    redirect('/membership/dashboard');
}

$tenantId = currentTenantId();
$userId = currentUserId();
$user = currentUser();

$membership = CustomerMembership::findActiveByUser($userId, $tenantId);
if (!$membership) {
    flashMessage('error', 'You do not have an active membership.');
    redirect('/membership');
}

$stripeCustomerId = $user['stripe_customer_id'] ?? $membership['stripe_customer_id'] ?? null;
if (!$stripeCustomerId) {
    flashMessage('error', 'Billing portal is not available. Please contact support.');
    redirect('/membership/dashboard');
}

try {
    $stripe = new StripeService(null, $tenantId);
    $returnUrl = url('/membership/dashboard');
    $session = $stripe->createMembershipPortalSession($stripeCustomerId, $returnUrl);
    redirect($session['url']);
} catch (\Exception $e) {
    flashMessage('error', 'Could not open billing portal. Please try again.');
    redirect('/membership/dashboard');
}
