<?php

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\StripeService;
use App\Services\BrevoService;

$tenant = currentTenant();
$tenantId = currentTenantId();

// Verify CSRF
$csrfToken = $_POST[CSRF_TOKEN_NAME] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    flashMessage('error', 'Ugyldig anmodning. Prøv venligst igen.');
    redirect('/checkout');
}

// Validate required fields
$name = sanitize($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$company = sanitize($_POST['company'] ?? '');
$addressLine1 = sanitize($_POST['address_line1'] ?? '');
$postalCode = sanitize($_POST['postal_code'] ?? '');
$city = sanitize($_POST['city'] ?? '');
$country = sanitize($_POST['country'] ?? 'DK');
$notes = sanitize($_POST['notes'] ?? '');
$paymentMethod = sanitize($_POST['payment_method'] ?? 'invoice');

$errors = [];
if (empty($name)) $errors[] = 'Navn er påkrævet.';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'En gyldig e-mailadresse er påkrævet.';
if (empty($addressLine1)) $errors[] = 'Adresse er påkrævet.';
if (empty($postalCode)) $errors[] = 'Postnummer er påkrævet.';
if (empty($city)) $errors[] = 'By er påkrævet.';

if (!empty($errors)) {
    flashMessage('error', implode(' ', $errors));
    redirect('/checkout');
}

// Load and validate cart
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    flashMessage('error', 'Din kurv er tom.');
    redirect('/kurv');
}

$cartItems = [];
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
        $subtotal += $lineTotal;
    }
}

if (empty($cartItems)) {
    flashMessage('error', 'Din kurv er tom.');
    redirect('/kurv');
}

$taxRate = 0.25; // 25% Danish VAT
$tax = round($subtotal * $taxRate, 2);
$total = round($subtotal + $tax, 2);

// Generate order number
$orderNumber = 'KZ-' . strtoupper(substr(md5($tenantId . time()), 0, 8));

$billingAddress = json_encode([
    'name' => $name,
    'company' => $company,
    'address_line1' => $addressLine1,
    'postal_code' => $postalCode,
    'city' => $city,
    'country' => $country,
]);

// Determine customer ID if logged in
$customerId = isAuthenticated() && isCustomer() ? currentUserId() : null;

// Create the order
$paymentReference = null;
$status = 'pending';

// If Stripe is configured and payment method is card
if ($paymentMethod === 'card') {
    try {
        $stripe = new StripeService(null, $tenantId);
        if ($stripe->isConfigured()) {
            // Amount in oerer (smallest DKK unit)
            $amountInOerer = (int)round($total * 100);
            $paymentIntent = $stripe->createPaymentIntent($amountInOerer, 'dkk', [
                'tenant_id' => $tenantId,
                'order_number' => $orderNumber,
            ]);
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
        flashMessage('error', 'Betalingsfejl. Prøv venligst igen.');
        redirect('/checkout');
    }
}

// For invoice payment, mark as pending
if ($paymentMethod === 'invoice') {
    $status = 'pending';
}

$orderId = Order::create([
    'tenant_id' => $tenantId,
    'order_number' => $orderNumber,
    'customer_id' => $customerId,
    'customer_name' => $name,
    'customer_email' => $email,
    'customer_phone' => $phone,
    'customer_company' => $company,
    'billing_address' => $billingAddress,
    'shipping_address' => $billingAddress,
    'subtotal_dkk' => $subtotal,
    'tax_dkk' => $tax,
    'shipping_dkk' => 0.00,
    'discount_dkk' => 0.00,
    'total_dkk' => $total,
    'currency' => 'DKK',
    'payment_method' => $paymentMethod,
    'payment_reference' => $paymentReference,
    'status' => $status,
    'notes' => $notes,
]);

// Create order items
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

// Send confirmation email via Brevo
try {
    $brevo = new BrevoService(null, $tenantId);
    if ($brevo->isConfigured()) {
        $subject = 'Ordrebekræftelse: ' . $orderNumber;
        $htmlContent = '<h2>Tak for din ordre!</h2>';
        $htmlContent .= '<p>Hej ' . h($name) . ',</p>';
        $htmlContent .= '<p>Vi har modtaget din ordre <strong>' . h($orderNumber) . '</strong>.</p>';
        $htmlContent .= '<p>Samlet beløb: ' . formatMoney($total) . ' (inkl. moms)</p>';
        if ($paymentMethod === 'invoice') {
            $htmlContent .= '<p>Du vil modtage en faktura på e-mail.</p>';
        }
        $htmlContent .= '<p>Vi vender tilbage med mere information snarest.</p>';

        $brevo->sendTransactionalEmail($email, $subject, $htmlContent);
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
]);

// Clear cart
unset($_SESSION['cart']);

// If Stripe card payment, redirect to payment page with client secret
if ($paymentMethod === 'card' && $paymentReference) {
    $_SESSION['pending_order_id'] = $orderId;
    $_SESSION['stripe_client_secret'] = $paymentIntent['client_secret'] ?? null;
    redirect('/checkout/betaling');
}

// For invoice, redirect to confirmation
flashMessage('success', 'Din ordre er modtaget! Du vil modtage en bekræftelse på e-mail.');
redirect('/konto/ordrer/' . $orderId);
