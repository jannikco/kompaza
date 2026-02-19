<?php

use App\Database\Database;
use App\Services\StripeService;

header('Content-Type: application/json');

$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

// Determine which Stripe secret to use
// First try tenant-specific, then platform default
$tenant = currentTenant();
$webhookSecret = $tenant['stripe_webhook_secret'] ?? STRIPE_WEBHOOK_SECRET;

if (!$webhookSecret) {
    http_response_code(400);
    echo json_encode(['error' => 'Webhook secret not configured']);
    exit;
}

$stripe = new StripeService($tenant['stripe_secret_key'] ?? null);
$event = $stripe->constructWebhookEvent($payload, $sigHeader);

if (!$event) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

$db = Database::getConnection();

switch ($event['type'] ?? '') {
    case 'payment_intent.succeeded':
        $paymentIntent = $event['data']['object'];
        $orderId = $paymentIntent['metadata']['order_id'] ?? null;
        if ($orderId) {
            $stmt = $db->prepare("UPDATE orders SET payment_status = 'paid', status = 'paid', paid_at = NOW() WHERE id = ? AND stripe_payment_intent_id = ?");
            $stmt->execute([$orderId, $paymentIntent['id']]);

            // Add status history
            $stmt = $db->prepare("INSERT INTO order_status_history (order_id, status, note) VALUES (?, 'paid', 'Payment received via Stripe')");
            $stmt->execute([$orderId]);
        }
        break;

    case 'payment_intent.payment_failed':
        $paymentIntent = $event['data']['object'];
        $orderId = $paymentIntent['metadata']['order_id'] ?? null;
        if ($orderId) {
            $stmt = $db->prepare("UPDATE orders SET payment_status = 'unpaid' WHERE id = ? AND stripe_payment_intent_id = ?");
            $stmt->execute([$orderId, $paymentIntent['id']]);
        }
        break;

    case 'customer.subscription.updated':
        $subscription = $event['data']['object'];
        $stripeSubId = $subscription['id'];
        $status = $subscription['status']; // active, past_due, cancelled, etc.

        $mappedStatus = match ($status) {
            'active' => 'active',
            'past_due' => 'past_due',
            'canceled', 'cancelled' => 'cancelled',
            'trialing' => 'trialing',
            default => 'active',
        };

        $stmt = $db->prepare("UPDATE tenants SET subscription_status = ? WHERE stripe_subscription_id = ?");
        $stmt->execute([$mappedStatus, $stripeSubId]);
        break;

    case 'customer.subscription.deleted':
        $subscription = $event['data']['object'];
        $stmt = $db->prepare("UPDATE tenants SET subscription_status = 'cancelled' WHERE stripe_subscription_id = ?");
        $stmt->execute([$subscription['id']]);
        break;

    case 'invoice.paid':
        // Subscription invoice paid — ensure tenant is active
        $invoice = $event['data']['object'];
        $stripeCustomerId = $invoice['customer'];
        $stmt = $db->prepare("UPDATE tenants SET subscription_status = 'active' WHERE stripe_customer_id = ?");
        $stmt->execute([$stripeCustomerId]);
        break;
}

http_response_code(200);
echo json_encode(['received' => true]);
