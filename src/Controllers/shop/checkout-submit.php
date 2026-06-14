<?php

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderBump;
use App\Models\UpsellOffer;
use App\Models\AbandonedCart;
use App\Services\StripeService;
use App\Services\EmailServiceFactory;

$tenant = currentTenant();
$tenantId = currentTenantId();

// Handle both JSON and form-encoded input
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (str_contains($contentType, 'application/json')) {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $isJson = true;
} else {
    $input = $_POST;
    $isJson = false;
}

// Helper to return error
$returnError = function($message) use ($isJson) {
    if ($isJson) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
    flashMessage('error', $message);
    redirect('/checkout');
};

// Verify CSRF
$csrfToken = $input[CSRF_TOKEN_NAME] ?? $input['csrf_token'] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    $returnError('Invalid request. Please try again.');
}

// Validate required fields
$name = sanitize($input['customer_name'] ?? $input['name'] ?? '');
$email = trim($input['customer_email'] ?? $input['email'] ?? '');
$phone = sanitize($input['customer_phone'] ?? $input['phone'] ?? '');
$company = sanitize($input['customer_company'] ?? $input['company'] ?? '');
$paymentMethod = sanitize($input['payment_method'] ?? 'invoice');
$paymentPlanType = sanitize($input['payment_plan'] ?? 'full');
$notes = sanitize($input['notes'] ?? '');

// Address handling - support both flat and nested formats
$shippingAddress = $input['shipping_address'] ?? null;
$billingAddress = $input['billing_address'] ?? null;
$addressLine1 = sanitize($input['address_line1'] ?? ($shippingAddress['street'] ?? ''));
$postalCode = sanitize($input['postal_code'] ?? ($shippingAddress['postal'] ?? ''));
$city = sanitize($input['city'] ?? ($shippingAddress['city'] ?? ''));
$country = sanitize($input['country'] ?? ($shippingAddress['country'] ?? 'DK'));

$errors = [];
if (empty($name)) $errors[] = 'Name is required.';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
if (empty($addressLine1)) $errors[] = 'Address is required.';
if (empty($postalCode)) $errors[] = 'Postal code is required.';
if (empty($city)) $errors[] = 'City is required.';

if (!empty($errors)) {
    $returnError(implode(' ', $errors));
}

// Load and validate cart
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    $returnError('Your cart is empty.');
}

$cartItems = [];
$cartProductIds = [];
$subtotal = 0;

foreach ($cart as $productId => $item) {
    $product = Product::find($productId, $tenantId);
    if ($product && $product['status'] === 'published') {
        $quantity = (int)($item['quantity'] ?? 1);
        $lineTotal = (float)$product['price_dkk'] * $quantity;
        $cartItems[] = [
            'product' => $product,
            'quantity' => $quantity,
            'line_total' => $lineTotal,
        ];
        $cartProductIds[] = (int)$product['id'];
        $subtotal += $lineTotal;
    }
}

if (empty($cartItems)) {
    $returnError('Your cart is empty.');
}

// Process order bumps
$selectedBumpIds = $input['order_bumps'] ?? [];
$bumpItems = [];
$bumpsTotal = 0;

if (!empty($selectedBumpIds)) {
    foreach ($selectedBumpIds as $bumpId) {
        $bump = OrderBump::find((int)$bumpId, $tenantId);
        if ($bump && $bump['status'] === 'active') {
            $bumpProduct = Product::find($bump['product_id'], $tenantId);
            if ($bumpProduct && $bumpProduct['status'] === 'published') {
                $bumpPrice = (float)$bump['bump_price_dkk'];
                $bumpItems[] = [
                    'bump' => $bump,
                    'product' => $bumpProduct,
                    'price' => $bumpPrice,
                ];
                $bumpsTotal += $bumpPrice;
                OrderBump::incrementAccepted($bump['id']);
            }
        }
    }
}

$subtotal += $bumpsTotal;

// Apply discount if provided
$discountCode = $input['discount_code'] ?? null;
$discountAmount = 0;
// TODO: validate discount code and calculate discount amount here if needed

$taxRate = 0.25; // 25% Danish VAT
$discountedSubtotal = max(0, $subtotal - $discountAmount);
$tax = round($discountedSubtotal * $taxRate, 2);
$total = round($discountedSubtotal + $tax, 2);

// Generate order number
$orderNumber = 'KZ-' . strtoupper(substr(md5($tenantId . time()), 0, 8));

$billingAddressJson = json_encode([
    'name' => $name,
    'company' => $company,
    'address_line1' => $addressLine1,
    'postal_code' => $postalCode,
    'city' => $city,
    'country' => $country,
]);

$shippingAddressJson = $billingAddressJson;
if ($billingAddress && $billingAddress !== ($shippingAddress ?? null)) {
    $billingAddressJson = json_encode([
        'name' => $name,
        'company' => $company,
        'address_line1' => sanitize($billingAddress['street'] ?? ''),
        'postal_code' => sanitize($billingAddress['postal'] ?? ''),
        'city' => sanitize($billingAddress['city'] ?? ''),
        'country' => sanitize($billingAddress['country'] ?? 'DK'),
    ]);
}

// Determine customer ID if logged in
$customerId = isAuthenticated() && isCustomer() ? currentUserId() : null;

// Check for installment payment plan
$installmentCount = null;
$stripeSubscriptionId = null;
$nextPaymentDate = null;

if ($paymentPlanType === 'installment' && count($cartItems) === 1) {
    $planProduct = $cartItems[0]['product'];
    if (!empty($planProduct['payment_plan_enabled'])) {
        $installmentCount = (int)$planProduct['installment_count'];
        $installmentPrice = $planProduct['installment_price_dkk']
            ? (float)$planProduct['installment_price_dkk']
            : round((float)$planProduct['price_dkk'] / $installmentCount, 2);
        // Recalculate totals for installment plan
        $subtotal = $installmentPrice; // First installment amount
        $bumpsTotal = 0; // No bumps for installment first payment
        foreach ($bumpItems as $bumpItem) {
            $bumpsTotal += $bumpItem['price'];
        }
        $subtotal += $bumpsTotal;
        $discountedSubtotal = max(0, $subtotal - $discountAmount);
        $tax = round($discountedSubtotal * $taxRate, 2);
        $total = round($discountedSubtotal + $tax, 2);
        $nextPaymentDate = date('Y-m-d', strtotime('+1 month'));
    }
}

// Create the order
$paymentReference = null;
$status = 'pending';

// If Stripe is configured and payment method is card
if ($paymentMethod === 'card') {
    try {
        $stripe = new StripeService(null, $tenantId);
        if ($stripe->isConfigured()) {
            // Create a Stripe customer so the card can be reused for one-click upsells
            $stripeCustomerId = null;
            try {
                $customer = $stripe->createCustomer($email, $name, [
                    'tenant_id' => $tenantId,
                    'order_number' => $orderNumber,
                ]);
                $stripeCustomerId = $customer['id'] ?? null;
            } catch (Exception $e) {
                // non-fatal: upsell charging just won't be available for this order
            }
            // Amount in oerer (smallest DKK unit)
            $amountInOerer = (int)round($total * 100);
            $paymentIntent = $stripe->createPaymentIntent($amountInOerer, 'dkk', [
                'tenant_id' => $tenantId,
                'order_number' => $orderNumber,
            ], true, $stripeCustomerId, $stripeCustomerId ? 'off_session' : null);
            $paymentReference = $paymentIntent['id'];
            $status = 'awaiting_payment';
        } else {
            // Stripe not configured, fall back to invoice
            $paymentMethod = 'invoice';
        }
    } catch (Exception $e) {
        if (APP_DEBUG) {
            error_log('Stripe payment intent failed: ' . $e->getMessage());
        }
        $returnError('Payment error. Please try again.');
    }
}

// For invoice payment, mark as pending
if ($paymentMethod === 'invoice') {
    $status = 'pending';
}

$orderData = [
    'tenant_id' => $tenantId,
    'order_number' => $orderNumber,
    'customer_id' => $customerId,
    'customer_name' => $name,
    'customer_email' => $email,
    'customer_phone' => $phone,
    'customer_company' => $company,
    'billing_address' => $billingAddressJson,
    'shipping_address' => $shippingAddressJson,
    'subtotal_dkk' => $subtotal,
    'tax_dkk' => $tax,
    'shipping_dkk' => 0.00,
    'discount_dkk' => $discountAmount,
    'total_dkk' => $total,
    'currency' => 'DKK',
    'payment_method' => $paymentMethod,
    'payment_reference' => $paymentReference,
    'status' => $status,
    'notes' => $notes,
];

// Add installment plan fields if applicable
if ($paymentPlanType === 'installment' && $installmentCount) {
    $orderData['payment_plan_type'] = 'installment';
    $orderData['installment_count'] = $installmentCount;
    $orderData['installments_paid'] = 0;
    $orderData['next_payment_date'] = $nextPaymentDate;
}

$orderId = Order::create($orderData);

// Create order items from cart
foreach ($cartItems as $cartItem) {
    $product = $cartItem['product'];
    OrderItem::create([
        'order_id' => $orderId,
        'product_id' => $product['id'],
        'product_name' => $product['name'],
        'product_sku' => $product['sku'] ?? null,
        'quantity' => $cartItem['quantity'],
        'unit_price_dkk' => $product['price_dkk'],
        'total_price_dkk' => $cartItem['line_total'],
        'is_digital' => $product['is_digital'] ?? 0,
        'digital_file_path' => $product['digital_file_path'] ?? null,
    ]);
}

// Create order items from bumps
foreach ($bumpItems as $bumpItem) {
    $bumpProduct = $bumpItem['product'];
    OrderItem::create([
        'order_id' => $orderId,
        'product_id' => $bumpProduct['id'],
        'product_name' => $bumpProduct['name'] . ' (Order Bump)',
        'product_sku' => $bumpProduct['sku'] ?? null,
        'quantity' => 1,
        'unit_price_dkk' => $bumpItem['price'],
        'total_price_dkk' => $bumpItem['price'],
        'is_digital' => $bumpProduct['is_digital'] ?? 0,
        'digital_file_path' => $bumpProduct['digital_file_path'] ?? null,
        'source' => 'order_bump',
        'order_bump_id' => $bumpItem['bump']['id'],
    ]);
}

// Mark abandoned cart as recovered if exists
$sessionId = session_id();
$abandonedCart = AbandonedCart::findBySession($sessionId, $tenantId);
if (!$abandonedCart && $email) {
    $abandonedCart = AbandonedCart::findByEmail($email, $tenantId);
}
if ($abandonedCart) {
    AbandonedCart::markRecovered($abandonedCart['id'], $orderId);
}

// Send confirmation email
try {
    $emailService = EmailServiceFactory::create();
    if ($emailService->isConfigured()) {
        $subject = 'Order Confirmation: ' . $orderNumber;
        $htmlContent = '<h2>Thank you for your order!</h2>';
        $htmlContent .= '<p>Hi ' . h($name) . ',</p>';
        $htmlContent .= '<p>We have received your order <strong>' . h($orderNumber) . '</strong>.</p>';
        $htmlContent .= '<p>Total: ' . formatMoney($total) . ' (incl. tax)</p>';
        if ($paymentMethod === 'invoice') {
            $htmlContent .= '<p>You will receive an invoice by email.</p>';
        }
        $htmlContent .= '<p>We will get back to you with more information shortly.</p>';

        $emailService->sendTransactionalEmail($email, $subject, $htmlContent);
    }
} catch (Exception $e) {
    if (APP_DEBUG) {
        error_log('Order confirmation email failed: ' . $e->getMessage());
    }
}

logAudit('order_created', 'order', $orderId, [
    'order_number' => $orderNumber,
    'total_dkk' => $total,
    'payment_method' => $paymentMethod,
    'bumps_count' => count($bumpItems),
]);

// Clear cart
unset($_SESSION['cart']);

// Check for upsell offers
$upsellOffer = UpsellOffer::findForPurchase($tenantId, $cartProductIds);

// Determine redirect
$redirectUrl = '/konto/ordrer/' . $orderId;

if ($paymentMethod === 'card' && $paymentReference) {
    $_SESSION['pending_order_id'] = $orderId;
    $_SESSION['stripe_client_secret'] = $paymentIntent['client_secret'] ?? null;
    $redirectUrl = '/checkout/betaling';
} elseif ($upsellOffer) {
    // Show upsell page for invoice orders
    $_SESSION['upsell_order_id'] = $orderId;
    $_SESSION['upsell_offer_id'] = $upsellOffer['id'];
    $redirectUrl = '/checkout/upsell';
}

if ($isJson) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'order_id' => $orderId,
        'order_number' => $orderNumber,
        'redirect' => $redirectUrl,
    ]);
    exit;
}

// Non-JSON fallback
if ($paymentMethod === 'card' && $paymentReference) {
    redirect('/checkout/betaling');
}

if ($upsellOffer) {
    redirect('/checkout/upsell');
}

flashMessage('success', 'Your order has been placed! You will receive a confirmation email.');
redirect('/konto/ordrer/' . $orderId);
