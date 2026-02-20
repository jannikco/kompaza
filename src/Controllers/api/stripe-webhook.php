<?php

use App\Services\StripeService;
use App\Models\TenantSubscription;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;

// Stripe sends raw JSON
$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = StripeService::constructWebhookEvent($payload, $sigHeader, STRIPE_WEBHOOK_SECRET);
} catch (\Exception $e) {
    error_log("Stripe webhook signature failed: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

header('Content-Type: application/json');

switch ($event->type) {
    case 'checkout.session.completed':
        $session = $event->data->object;
        if ($session->mode === 'subscription') {
            $sub = TenantSubscription::findByStripeCustomerId($session->customer);
            if ($sub) {
                TenantSubscription::update($sub['id'], [
                    'stripe_subscription_id' => $session->subscription,
                    'status' => 'active',
                ]);
            }
        }
        break;

    case 'customer.subscription.created':
    case 'customer.subscription.updated':
        $subscription = $event->data->object;
        $sub = TenantSubscription::findByStripeSubscriptionId($subscription->id);
        if (!$sub) {
            $sub = TenantSubscription::findByStripeCustomerId($subscription->customer);
        }
        if ($sub) {
            // Try to match plan by price ID
            $priceId = $subscription->items->data[0]->price->id ?? null;
            $planId = $sub['plan_id'];
            if ($priceId) {
                $plans = SubscriptionPlan::all();
                foreach ($plans as $plan) {
                    if ($plan['stripe_price_monthly_id'] === $priceId || $plan['stripe_price_annual_id'] === $priceId) {
                        $planId = $plan['id'];
                        break;
                    }
                }
            }

            $interval = ($subscription->items->data[0]->price->recurring->interval ?? 'month') === 'year' ? 'annual' : 'monthly';

            TenantSubscription::update($sub['id'], [
                'stripe_subscription_id' => $subscription->id,
                'plan_id' => $planId,
                'billing_interval' => $interval,
                'status' => $subscription->status,
                'current_period_start' => date('Y-m-d H:i:s', $subscription->current_period_start),
                'current_period_end' => date('Y-m-d H:i:s', $subscription->current_period_end),
                'cancel_at_period_end' => $subscription->cancel_at_period_end ? 1 : 0,
                'canceled_at' => $subscription->canceled_at ? date('Y-m-d H:i:s', $subscription->canceled_at) : null,
                'trial_ends_at' => $subscription->trial_end ? date('Y-m-d H:i:s', $subscription->trial_end) : null,
            ]);
        }
        break;

    case 'customer.subscription.deleted':
        $subscription = $event->data->object;
        TenantSubscription::updateByStripeSubscriptionId($subscription->id, [
            'status' => 'canceled',
            'canceled_at' => date('Y-m-d H:i:s'),
        ]);
        break;

    case 'invoice.paid':
        $invoice = $event->data->object;
        $sub = TenantSubscription::findByStripeCustomerId($invoice->customer);
        if ($sub) {
            $existing = SubscriptionInvoice::findByStripeInvoiceId($invoice->id);
            $invoiceData = [
                'stripe_charge_id' => $invoice->charge,
                'amount_cents' => $invoice->amount_paid,
                'currency' => $invoice->currency,
                'status' => 'paid',
                'invoice_url' => $invoice->hosted_invoice_url,
                'invoice_pdf' => $invoice->invoice_pdf,
                'period_start' => date('Y-m-d H:i:s', $invoice->period_start),
                'period_end' => date('Y-m-d H:i:s', $invoice->period_end),
                'paid_at' => date('Y-m-d H:i:s'),
            ];
            if ($existing) {
                SubscriptionInvoice::updateByStripeInvoiceId($invoice->id, $invoiceData);
            } else {
                $invoiceData['tenant_id'] = $sub['tenant_id'];
                $invoiceData['stripe_invoice_id'] = $invoice->id;
                SubscriptionInvoice::create($invoiceData);
            }
        }
        break;

    case 'invoice.payment_failed':
        $invoice = $event->data->object;
        $sub = TenantSubscription::findByStripeCustomerId($invoice->customer);
        if ($sub) {
            TenantSubscription::update($sub['id'], ['status' => 'past_due']);
            $existing = SubscriptionInvoice::findByStripeInvoiceId($invoice->id);
            if ($existing) {
                SubscriptionInvoice::updateByStripeInvoiceId($invoice->id, ['status' => 'failed']);
            } else {
                SubscriptionInvoice::create([
                    'tenant_id' => $sub['tenant_id'],
                    'stripe_invoice_id' => $invoice->id,
                    'amount_cents' => $invoice->amount_due,
                    'currency' => $invoice->currency,
                    'status' => 'failed',
                    'invoice_url' => $invoice->hosted_invoice_url,
                ]);
            }
        }
        break;
}

echo json_encode(['received' => true]);
exit;
