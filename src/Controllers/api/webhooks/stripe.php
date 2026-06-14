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

            // Handle course purchase enrollment
            $type = $paymentIntent['metadata']['type'] ?? '';
            if ($type === 'course_purchase') {
                $courseId = $paymentIntent['metadata']['course_id'] ?? null;
                $tenantIdMeta = $paymentIntent['metadata']['tenant_id'] ?? null;
                if ($courseId && $orderId) {
                    // Find user from order
                    $stmt = $db->prepare("SELECT customer_id FROM orders WHERE id = ?");
                    $stmt->execute([$orderId]);
                    $order = $stmt->fetch();
                    if ($order && $order['customer_id']) {
                        // Check if already enrolled
                        $stmt = $db->prepare("SELECT id FROM course_enrollments WHERE course_id = ? AND user_id = ?");
                        $stmt->execute([$courseId, $order['customer_id']]);
                        $existing = $stmt->fetch();
                        if (!$existing) {
                            $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM course_lessons WHERE course_id = ?");
                            $stmt->execute([$courseId]);
                            $totalLessons = $stmt->fetch()['cnt'];

                            $stmt = $db->prepare("INSERT INTO course_enrollments (tenant_id, course_id, user_id, enrollment_source, order_id, status, total_lessons, enrolled_at) VALUES (?, ?, ?, 'purchase', ?, 'active', ?, NOW())");
                            $stmt->execute([$tenantIdMeta, $courseId, $order['customer_id'], $orderId, $totalLessons]);

                            $stmt = $db->prepare("UPDATE courses SET enrollment_count = enrollment_count + 1 WHERE id = ?");
                            $stmt->execute([$courseId]);
                        }
                    }
                }
            }
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
        $status = $subscription['status'];

        $mappedStatus = match ($status) {
            'active' => 'active',
            'past_due' => 'past_due',
            'canceled', 'cancelled' => 'cancelled',
            'trialing' => 'trialing',
            default => 'active',
        };

        // Update tenant subscription (platform subscription)
        $stmt = $db->prepare("UPDATE tenants SET subscription_status = ? WHERE stripe_subscription_id = ?");
        $stmt->execute([$mappedStatus, $stripeSubId]);

        // Update customer membership if this is a membership subscription
        $stmt = $db->prepare("SELECT cm.*, mp.tier_level FROM customer_memberships cm JOIN membership_plans mp ON cm.plan_id = mp.id WHERE cm.stripe_subscription_id = ?");
        $stmt->execute([$stripeSubId]);
        $membership = $stmt->fetch();
        if ($membership) {
            $stmt = $db->prepare("UPDATE customer_memberships SET status = ?, current_period_start = ?, current_period_end = ? WHERE id = ?");
            $periodStart = isset($subscription['current_period_start']) ? date('Y-m-d H:i:s', $subscription['current_period_start']) : null;
            $periodEnd = isset($subscription['current_period_end']) ? date('Y-m-d H:i:s', $subscription['current_period_end']) : null;
            $stmt->execute([$mappedStatus, $periodStart, $periodEnd, $membership['id']]);

            $stmt = $db->prepare("INSERT INTO membership_events (tenant_id, user_id, membership_id, event_type, stripe_event_id) VALUES (?, ?, ?, 'membership_updated', ?)");
            $stmt->execute([$membership['tenant_id'], $membership['user_id'], $membership['id'], $event['id'] ?? null]);
        }
        break;

    case 'customer.subscription.deleted':
        $subscription = $event['data']['object'];
        $stmt = $db->prepare("UPDATE tenants SET subscription_status = 'cancelled' WHERE stripe_subscription_id = ?");
        $stmt->execute([$subscription['id']]);

        // Cancel course enrollments tied to this subscription
        $stmt = $db->prepare("UPDATE course_enrollments SET status = 'cancelled' WHERE stripe_subscription_id = ? AND status = 'active'");
        $stmt->execute([$subscription['id']]);

        // Cancel customer membership if this is a membership subscription
        $stmt = $db->prepare("SELECT * FROM customer_memberships WHERE stripe_subscription_id = ?");
        $stmt->execute([$subscription['id']]);
        $membership = $stmt->fetch();
        if ($membership) {
            $stmt = $db->prepare("UPDATE customer_memberships SET status = 'cancelled', cancelled_at = NOW() WHERE id = ?");
            $stmt->execute([$membership['id']]);

            // Revoke membership-based course enrollments
            $stmt = $db->prepare("UPDATE course_enrollments SET status = 'cancelled' WHERE user_id = ? AND tenant_id = ? AND enrollment_source = 'membership' AND status = 'active'");
            $stmt->execute([$membership['user_id'], $membership['tenant_id']]);

            $stmt = $db->prepare("INSERT INTO membership_events (tenant_id, user_id, membership_id, event_type, stripe_event_id) VALUES (?, ?, ?, 'membership_cancelled', ?)");
            $stmt->execute([$membership['tenant_id'], $membership['user_id'], $membership['id'], $event['id'] ?? null]);
        }
        break;

    case 'checkout.session.completed':
        $session = $event['data']['object'];
        $type = $session['metadata']['type'] ?? '';

        // Membership checkout
        if ($type === 'membership' && ($session['mode'] ?? '') === 'subscription') {
            $planId = $session['metadata']['plan_id'] ?? null;
            $userId = $session['metadata']['user_id'] ?? null;
            $tenantIdMeta = $session['metadata']['tenant_id'] ?? null;
            $billingInterval = $session['metadata']['billing_interval'] ?? 'monthly';
            $stripeSubId = $session['subscription'] ?? null;
            $stripeCustomerId = $session['customer'] ?? null;

            if ($planId && $userId && $tenantIdMeta) {
                // Check if already has membership
                $stmt = $db->prepare("SELECT id FROM customer_memberships WHERE user_id = ? AND tenant_id = ?");
                $stmt->execute([$userId, $tenantIdMeta]);
                $existing = $stmt->fetch();

                if ($existing) {
                    $stmt = $db->prepare("UPDATE customer_memberships SET plan_id = ?, stripe_subscription_id = ?, stripe_customer_id = ?, billing_interval = ?, status = 'active', current_period_start = NOW(), cancelled_at = NULL WHERE id = ?");
                    $stmt->execute([$planId, $stripeSubId, $stripeCustomerId, $billingInterval, $existing['id']]);
                } else {
                    $stmt = $db->prepare("INSERT INTO customer_memberships (tenant_id, user_id, plan_id, stripe_subscription_id, stripe_customer_id, billing_interval, status, current_period_start) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())");
                    $stmt->execute([$tenantIdMeta, $userId, $planId, $stripeSubId, $stripeCustomerId, $billingInterval]);
                }

                // Log event
                $stmt = $db->prepare("INSERT INTO membership_events (tenant_id, user_id, event_type, stripe_event_id, payload) VALUES (?, ?, 'membership_started', ?, ?)");
                $stmt->execute([$tenantIdMeta, $userId, $event['id'] ?? null, json_encode(['plan_id' => $planId, 'billing_interval' => $billingInterval])]);

                // Auto-enroll in courses for unlimited plans (premium)
                $stmt = $db->prepare("SELECT max_courses FROM membership_plans WHERE id = ?");
                $stmt->execute([$planId]);
                $plan = $stmt->fetch();
                if ($plan && $plan['max_courses'] === null) {
                    // Unlimited: enroll in all membership courses at their tier or below
                    $stmt = $db->prepare("SELECT mp.tier_level FROM membership_plans mp WHERE mp.id = ?");
                    $stmt->execute([$planId]);
                    $planData = $stmt->fetch();
                    if ($planData) {
                        $stmt = $db->prepare("SELECT id FROM courses WHERE tenant_id = ? AND membership_tier_level IS NOT NULL AND membership_tier_level <= ?");
                        $stmt->execute([$tenantIdMeta, $planData['tier_level']]);
                        $courses = $stmt->fetchAll();
                        foreach ($courses as $c) {
                            $stmt2 = $db->prepare("SELECT id FROM course_enrollments WHERE course_id = ? AND user_id = ?");
                            $stmt2->execute([$c['id'], $userId]);
                            if (!$stmt2->fetch()) {
                                $stmt3 = $db->prepare("SELECT COUNT(*) as cnt FROM course_lessons WHERE course_id = ?");
                                $stmt3->execute([$c['id']]);
                                $total = $stmt3->fetch()['cnt'];
                                $stmt4 = $db->prepare("INSERT INTO course_enrollments (tenant_id, course_id, user_id, enrollment_source, status, total_lessons, enrolled_at) VALUES (?, ?, ?, 'membership', 'active', ?, NOW())");
                                $stmt4->execute([$tenantIdMeta, $c['id'], $userId, $total]);
                            }
                        }
                    }
                }
            }
        }

        if ($type === 'course_subscription' && ($session['mode'] ?? '') === 'subscription') {
            $courseId = $session['metadata']['course_id'] ?? null;
            $userId = $session['metadata']['user_id'] ?? null;
            $tenantIdMeta = $session['metadata']['tenant_id'] ?? null;
            $stripeSubId = $session['subscription'] ?? null;

            if ($courseId && $userId && $tenantIdMeta) {
                $stmt = $db->prepare("SELECT id FROM course_enrollments WHERE course_id = ? AND user_id = ?");
                $stmt->execute([$courseId, $userId]);
                $existing = $stmt->fetch();
                if (!$existing) {
                    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM course_lessons WHERE course_id = ?");
                    $stmt->execute([$courseId]);
                    $totalLessons = $stmt->fetch()['cnt'];

                    $stmt = $db->prepare("INSERT INTO course_enrollments (tenant_id, course_id, user_id, enrollment_source, stripe_subscription_id, status, total_lessons, enrolled_at) VALUES (?, ?, ?, 'subscription', ?, 'active', ?, NOW())");
                    $stmt->execute([$tenantIdMeta, $courseId, $userId, $stripeSubId, $totalLessons]);

                    $stmt = $db->prepare("UPDATE courses SET enrollment_count = enrollment_count + 1 WHERE id = ?");
                    $stmt->execute([$courseId]);
                } elseif ($stripeSubId) {
                    $stmt = $db->prepare("UPDATE course_enrollments SET status = 'active', stripe_subscription_id = ? WHERE id = ?");
                    $stmt->execute([$stripeSubId, $existing['id']]);
                }
            }
        }
        break;

    case 'invoice.paid':
        // Subscription invoice paid — ensure tenant is active
        $invoice = $event['data']['object'];
        $stripeCustomerId = $invoice['customer'];
        $stmt = $db->prepare("UPDATE tenants SET subscription_status = 'active' WHERE stripe_customer_id = ?");
        $stmt->execute([$stripeCustomerId]);

        // Extend membership period if this is a membership invoice
        $stripeSubId = $invoice['subscription'] ?? null;
        if ($stripeSubId) {
            $stmt = $db->prepare("SELECT * FROM customer_memberships WHERE stripe_subscription_id = ?");
            $stmt->execute([$stripeSubId]);
            $membership = $stmt->fetch();
            if ($membership) {
                $periodEnd = isset($invoice['lines']['data'][0]['period']['end']) ? date('Y-m-d H:i:s', $invoice['lines']['data'][0]['period']['end']) : null;
                if ($periodEnd) {
                    $stmt = $db->prepare("UPDATE customer_memberships SET status = 'active', current_period_end = ? WHERE id = ?");
                    $stmt->execute([$periodEnd, $membership['id']]);
                }
            }
        }
        break;

    case 'invoice.payment_failed':
        $invoice = $event['data']['object'];
        $orderId = $invoice['metadata']['order_id'] ?? null;
        if ($orderId) {
            $stmt = $db->prepare("UPDATE orders SET payment_status = 'unpaid' WHERE id = ?");
            $stmt->execute([$orderId]);
        }

        // Mark membership as past_due
        $stripeSubId = $invoice['subscription'] ?? null;
        if ($stripeSubId) {
            $stmt = $db->prepare("SELECT * FROM customer_memberships WHERE stripe_subscription_id = ?");
            $stmt->execute([$stripeSubId]);
            $membership = $stmt->fetch();
            if ($membership) {
                $stmt = $db->prepare("UPDATE customer_memberships SET status = 'past_due' WHERE id = ?");
                $stmt->execute([$membership['id']]);

                $stmt = $db->prepare("INSERT INTO membership_events (tenant_id, user_id, membership_id, event_type, stripe_event_id) VALUES (?, ?, ?, 'payment_failed', ?)");
                $stmt->execute([$membership['tenant_id'], $membership['user_id'], $membership['id'], $event['id'] ?? null]);
            }
        }
        break;
}

http_response_code(200);
echo json_encode(['received' => true]);
