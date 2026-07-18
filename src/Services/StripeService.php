<?php

namespace App\Services;

use App\Models\Setting;

class StripeService {
    private string $secretKey;
    private string $apiBase = 'https://api.stripe.com/v1';

    /**
     * Constructor.
     * Priority: explicit $secretKey > per-tenant Stripe key > platform default STRIPE_SECRET_KEY constant.
     */
    public function __construct(?string $secretKey = null, ?int $tenantId = null) {
        if ($secretKey) {
            $this->secretKey = $secretKey;
        } elseif ($tenantId) {
            $tenantKey = Setting::get('stripe_secret_key', $tenantId);
            $this->secretKey = !empty($tenantKey) ? $tenantKey : (defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '');
        } else {
            $tenant = TenantResolver::current();
            if ($tenant) {
                $tenantKey = Setting::get('stripe_secret_key', $tenant['id']);
                $this->secretKey = !empty($tenantKey) ? $tenantKey : (defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '');
            } else {
                $this->secretKey = defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '';
            }
        }
    }

    /**
     * Create a PaymentIntent.
     *
     * @param int    $amount   Amount in smallest currency unit (e.g. orer for DKK)
     * @param string $currency ISO currency code (default: dkk)
     * @param array  $metadata Key-value metadata to attach
     * @return array Stripe PaymentIntent object
     * @throws \Exception on failure
     */
    public function createPaymentIntent(int $amount, string $currency = 'dkk', array $metadata = [], bool $usePaymentElement = true, ?string $customerId = null, ?string $setupFutureUsage = null): array {
        $params = [
            'amount' => $amount,
            'currency' => strtolower($currency),
        ];

        // Enable automatic payment methods for Payment Element
        if ($usePaymentElement) {
            $params['automatic_payment_methods[enabled]'] = 'true';
        }

        // Attach a customer + save the method for later off-session charges (post-purchase upsells)
        if ($customerId) {
            $params['customer'] = $customerId;
        }
        if ($setupFutureUsage) {
            $params['setup_future_usage'] = $setupFutureUsage;
        }

        if (!empty($metadata)) {
            foreach ($metadata as $key => $value) {
                $params["metadata[{$key}]"] = $value;
            }
        }

        return $this->makeRequest('POST', '/payment_intents', $params);
    }

    /**
     * Charge a saved payment method off-session (used for one-click post-purchase upsells).
     * Throws on failure; the caller must handle SCA/decline by not granting the item.
     *
     * @return array Stripe PaymentIntent object (status 'succeeded' on success)
     */
    public function chargeOffSession(int $amount, string $currency, string $customerId, string $paymentMethodId, array $metadata = []): array {
        $params = [
            'amount' => $amount,
            'currency' => strtolower($currency),
            'customer' => $customerId,
            'payment_method' => $paymentMethodId,
            'off_session' => 'true',
            'confirm' => 'true',
        ];
        foreach ($metadata as $key => $value) {
            $params["metadata[{$key}]"] = $value;
        }
        return $this->makeRequest('POST', '/payment_intents', $params);
    }

    /**
     * Create a hosted Checkout Session to sell a single ebook through a tenant's
     * Stripe Connect account (destination charge with an application fee).
     *
     * @return array Stripe Checkout Session object (use ['id'] and ['url'])
     */
    public function createEbookCheckoutSession(string $name, int $amountCents, string $currency, int $feeCents, string $connectAccountId, string $successUrl, string $cancelUrl, array $metadata = []): array {
        $params = [
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'line_items[0][quantity]' => 1,
            'line_items[0][price_data][currency]' => strtolower($currency),
            'line_items[0][price_data][unit_amount]' => $amountCents,
            'line_items[0][price_data][product_data][name]' => $name,
            'payment_intent_data[application_fee_amount]' => $feeCents,
            'payment_intent_data[transfer_data][destination]' => $connectAccountId,
        ];
        foreach ($metadata as $key => $value) {
            $params["metadata[{$key}]"] = $value;
            $params["payment_intent_data[metadata][{$key}]"] = $value;
        }
        return $this->makeRequest('POST', '/checkout/sessions', $params);
    }

    /**
     * Retrieve an existing PaymentIntent.
     *
     * @param string $paymentIntentId The PaymentIntent ID (pi_xxx)
     * @return array Stripe PaymentIntent object
     * @throws \Exception on failure
     */
    public function retrievePaymentIntent(string $paymentIntentId): array {
        return $this->makeRequest('GET', '/payment_intents/' . urlencode($paymentIntentId));
    }

    /**
     * Create a Stripe Customer.
     *
     * @param string $email    Customer email
     * @param string $name     Customer name
     * @param array  $metadata Key-value metadata
     * @return array Stripe Customer object
     * @throws \Exception on failure
     */
    public function createCustomer(string $email, string $name = '', array $metadata = []): array {
        $params = [
            'email' => $email,
        ];

        if (!empty($name)) {
            $params['name'] = $name;
        }

        if (!empty($metadata)) {
            foreach ($metadata as $key => $value) {
                $params["metadata[{$key}]"] = $value;
            }
        }

        return $this->makeRequest('POST', '/customers', $params);
    }

    /**
     * Create a Subscription for a customer.
     *
     * @param string   $customerId Stripe Customer ID (cus_xxx)
     * @param string   $priceId    Stripe Price ID (price_xxx)
     * @param int|null $trialDays  Number of trial days (null = no trial)
     * @return array Stripe Subscription object
     * @throws \Exception on failure
     */
    public function createSubscription(string $customerId, string $priceId, ?int $trialDays = null): array {
        $params = [
            'customer' => $customerId,
            'items[0][price]' => $priceId,
            'payment_behavior' => 'default_incomplete',
            'expand[]' => 'latest_invoice.payment_intent',
        ];

        if ($trialDays !== null && $trialDays > 0) {
            $params['trial_period_days'] = $trialDays;
        }

        return $this->makeRequest('POST', '/subscriptions', $params);
    }

    /**
     * Cancel a Subscription.
     *
     * @param string $subscriptionId Stripe Subscription ID (sub_xxx)
     * @return array Stripe Subscription object (cancelled)
     * @throws \Exception on failure
     */
    public function cancelSubscription(string $subscriptionId): array {
        return $this->makeRequest('DELETE', '/subscriptions/' . urlencode($subscriptionId));
    }

    /**
     * Validate and construct a webhook event from the raw payload and Stripe signature header.
     *
     * @param string $payload   Raw request body
     * @param string $sigHeader Value of Stripe-Signature header
     * @param string|null $webhookSecret  Webhook endpoint secret (falls back to tenant/platform config)
     * @return array Parsed event data
     * @throws \Exception if signature validation fails
     */
    public function constructWebhookEvent(string $payload, string $sigHeader, ?string $webhookSecret = null): array {
        $secret = $webhookSecret ?? $this->resolveWebhookSecret();

        if (empty($secret)) {
            throw new \Exception('Stripe webhook secret is not configured.');
        }

        // Parse the Stripe-Signature header
        $sigParts = [];
        foreach (explode(',', $sigHeader) as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) === 2) {
                $sigParts[$kv[0]] = $kv[1];
            }
        }

        if (!isset($sigParts['t']) || !isset($sigParts['v1'])) {
            throw new \Exception('Invalid Stripe-Signature header format.');
        }

        $timestamp = $sigParts['t'];
        $signature = $sigParts['v1'];

        // Verify signature: HMAC SHA256 of "{timestamp}.{payload}" using the webhook secret
        $signedPayload = $timestamp . '.' . $payload;
        $expectedSignature = hash_hmac('sha256', $signedPayload, $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            throw new \Exception('Stripe webhook signature verification failed.');
        }

        // Check timestamp tolerance (allow up to 5 minutes)
        $tolerance = 300;
        if (abs(time() - (int) $timestamp) > $tolerance) {
            throw new \Exception('Stripe webhook timestamp is outside the tolerance zone.');
        }

        $event = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to decode Stripe webhook payload: ' . json_last_error_msg());
        }

        return $event;
    }

    /**
     * Create a one-time Stripe Checkout Session (platform or tenant secret key).
     * Used for course/ebook purchases without Connect destination charges.
     *
     * @param string      $name         Line item name
     * @param int         $amountCents  Amount in smallest currency unit
     * @param string      $currency     ISO currency
     * @param string      $successUrl   Success redirect (may include {CHECKOUT_SESSION_ID})
     * @param string      $cancelUrl    Cancel redirect
     * @param array       $metadata     Session metadata
     * @param string|null $customerEmail Prefill email
     */
    public function createOneTimeCheckoutSession(
        string $name,
        int $amountCents,
        string $currency,
        string $successUrl,
        string $cancelUrl,
        array $metadata = [],
        ?string $customerEmail = null
    ): array {
        $params = [
            'mode' => 'payment',
            'line_items[0][price_data][currency]' => strtolower($currency),
            'line_items[0][price_data][unit_amount]' => $amountCents,
            'line_items[0][price_data][product_data][name]' => $name,
            'line_items[0][quantity]' => 1,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ];
        if ($customerEmail) {
            $params['customer_email'] = $customerEmail;
        }
        foreach ($metadata as $key => $value) {
            $params["metadata[{$key}]"] = $value;
        }
        return $this->makeRequest('POST', '/checkout/sessions', $params);
    }

    /**
     * Retrieve a Checkout Session by ID.
     */
    public function retrieveCheckoutSession(string $sessionId): array {
        return $this->makeRequest('GET', '/checkout/sessions/' . urlencode($sessionId));
    }

    /**
     * Subscription Checkout with inline price_data (for installment plans without pre-created Price IDs).
     */
    public function createInstallmentCheckoutSession(
        string $name,
        int $amountCentsPerMonth,
        string $currency,
        string $successUrl,
        string $cancelUrl,
        array $metadata = [],
        ?string $customerEmail = null,
        int $maxInstallments = 3
    ): array {
        $params = [
            'mode' => 'subscription',
            'line_items[0][price_data][currency]' => strtolower($currency),
            'line_items[0][price_data][unit_amount]' => $amountCentsPerMonth,
            'line_items[0][price_data][recurring][interval]' => 'month',
            'line_items[0][price_data][product_data][name]' => $name . " ({$maxInstallments} installments)",
            'line_items[0][quantity]' => 1,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ];
        if ($customerEmail) {
            $params['customer_email'] = $customerEmail;
        }
        $metadata['max_installments'] = (string)$maxInstallments;
        $metadata['installment_plan'] = '1';
        foreach ($metadata as $key => $value) {
            $params["metadata[{$key}]"] = $value;
            $params["subscription_data[metadata][{$key}]"] = $value;
        }
        return $this->makeRequest('POST', '/checkout/sessions', $params);
    }

    /**
     * Create a Stripe Checkout Session for membership subscription.
     */
    public function createMembershipCheckoutSession(
        string $customerId,
        string $priceId,
        array $metadata,
        string $successUrl,
        string $cancelUrl
    ): array {
        $params = [
            'customer' => $customerId,
            'mode' => 'subscription',
            'line_items[0][price]' => $priceId,
            'line_items[0][quantity]' => 1,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ];

        foreach ($metadata as $key => $value) {
            $params["metadata[{$key}]"] = $value;
            $params["subscription_data[metadata][{$key}]"] = $value;
        }

        return $this->makeRequest('POST', '/checkout/sessions', $params);
    }

    /**
     * Create a Stripe Customer Portal session for managing billing.
     */
    public function createMembershipPortalSession(string $customerId, string $returnUrl): array {
        return $this->makeRequest('POST', '/billing_portal/sessions', [
            'customer' => $customerId,
            'return_url' => $returnUrl,
        ]);
    }

    /**
     * Alias for platform SaaS billing portal (same Stripe Billing Portal API).
     */
    public function createBillingPortalSession(string $customerId, string $returnUrl): array {
        return $this->createMembershipPortalSession($customerId, $returnUrl);
    }

    /**
     * Create a Stripe Checkout Session for platform SaaS subscription.
     *
     * @param string   $customerId Stripe Customer ID
     * @param string   $priceId    Stripe Price ID
     * @param string   $successUrl Success redirect URL
     * @param string   $cancelUrl  Cancel redirect URL
     * @param int|null $trialDays  Optional trial period in days
     * @param array    $metadata   Session/subscription metadata
     * @return array Stripe Checkout Session
     */
    public function createSubscriptionCheckout(
        string $customerId,
        string $priceId,
        string $successUrl,
        string $cancelUrl,
        ?int $trialDays = null,
        array $metadata = []
    ): array {
        $params = [
            'customer' => $customerId,
            'mode' => 'subscription',
            'line_items[0][price]' => $priceId,
            'line_items[0][quantity]' => 1,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ];

        if ($trialDays !== null && $trialDays > 0) {
            $params['subscription_data[trial_period_days]'] = $trialDays;
        }

        foreach ($metadata as $key => $value) {
            $params["metadata[{$key}]"] = $value;
            $params["subscription_data[metadata][{$key}]"] = $value;
        }

        return $this->makeRequest('POST', '/checkout/sessions', $params);
    }

    /**
     * Create a Stripe Connect Express account.
     *
     * @param string $email    Account email
     * @param array  $metadata Metadata to attach
     * @param string $country  Two-letter country code (default DK)
     * @return array Stripe Account object
     */
    public function createConnectAccount(string $email, array $metadata = [], string $country = 'DK'): array {
        $params = [
            'type' => 'express',
            'email' => $email,
            'country' => strtoupper($country),
            'capabilities[card_payments][requested]' => 'true',
            'capabilities[transfers][requested]' => 'true',
        ];

        foreach ($metadata as $key => $value) {
            $params["metadata[{$key}]"] = $value;
        }

        return $this->makeRequest('POST', '/accounts', $params);
    }

    /**
     * Create an Account Link for Stripe Connect onboarding.
     *
     * @param string $accountId  Connected account ID (acct_xxx)
     * @param string $refreshUrl URL if the user needs to restart onboarding
     * @param string $returnUrl  URL after completing onboarding
     * @return array Stripe AccountLink object (includes url)
     */
    public function createAccountLink(string $accountId, string $refreshUrl, string $returnUrl): array {
        return $this->makeRequest('POST', '/account_links', [
            'account' => $accountId,
            'refresh_url' => $refreshUrl,
            'return_url' => $returnUrl,
            'type' => 'account_onboarding',
        ]);
    }

    /**
     * Retrieve a Stripe Connect account.
     */
    public function retrieveAccount(string $accountId): array {
        return $this->makeRequest('GET', '/accounts/' . urlencode($accountId));
    }

    /**
     * Create a Stripe Express Dashboard login link for a connected account.
     */
    public function createConnectLoginLink(string $accountId): array {
        return $this->makeRequest('POST', '/accounts/' . urlencode($accountId) . '/login_links');
    }

    /**
     * Retrieve a Stripe Subscription.
     */
    public function retrieveSubscription(string $subscriptionId): array {
        return $this->makeRequest('GET', '/subscriptions/' . urlencode($subscriptionId));
    }

    /**
     * Check if this service instance has a valid secret key configured.
     */
    public function isConfigured(): bool {
        return !empty($this->secretKey);
    }

    /**
     * Get the publishable key for the frontend.
     */
    public static function getPublishableKey(?int $tenantId = null): string {
        if ($tenantId) {
            $tenantKey = \App\Models\Setting::get('stripe_publishable_key', $tenantId);
            if (!empty($tenantKey)) return $tenantKey;
        }
        return defined('STRIPE_PUBLISHABLE_KEY') ? STRIPE_PUBLISHABLE_KEY : '';
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Resolve webhook secret from tenant setting or platform default.
     */
    private function resolveWebhookSecret(): string {
        $tenant = TenantResolver::current();
        if ($tenant) {
            $tenantSecret = Setting::get('stripe_webhook_secret', $tenant['id']);
            if (!empty($tenantSecret)) {
                return $tenantSecret;
            }
        }
        return defined('STRIPE_WEBHOOK_SECRET') ? STRIPE_WEBHOOK_SECRET : '';
    }

    /**
     * Make an authenticated request to the Stripe API.
     *
     * Stripe uses form-encoded POST bodies and Bearer token auth.
     *
     * @param string     $method   HTTP method (GET, POST, DELETE)
     * @param string     $endpoint API endpoint path (e.g. /payment_intents)
     * @param array|null $params   Request parameters (form-encoded for POST)
     * @return array Decoded JSON response
     * @throws \Exception on cURL or HTTP error
     */
    private function makeRequest(string $method, string $endpoint, ?array $params = null): array {
        $url = $this->apiBase . $endpoint;

        // For GET requests, append params as query string
        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/x-www-form-urlencoded',
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($params !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \Exception('Stripe cURL error: ' . $curlError);
        }

        $responseData = json_decode($response, true) ?? [];

        if ($httpCode >= 400) {
            $errorMessage = $responseData['error']['message'] ?? 'Unknown error';
            $errorType = $responseData['error']['type'] ?? 'api_error';
            throw new \Exception("Stripe API error (HTTP {$httpCode}): [{$errorType}] {$errorMessage}");
        }

        return $responseData;
    }
}
