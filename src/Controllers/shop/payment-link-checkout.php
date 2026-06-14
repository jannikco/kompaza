<?php

use App\Models\PaymentLink;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\StripeService;
use App\Services\EmailServiceFactory;

if (!isPost()) redirect('/');

$csrfToken = $_POST[CSRF_TOKEN_NAME] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    flashMessage('error', 'Invalid request. Please try again.');
    redirect('/');
}

$token = sanitize($_POST['token'] ?? '');
$link = PaymentLink::findByToken($token);
if (!$link || !PaymentLink::isValid($link)) {
    flashMessage('error', 'This payment link is no longer valid.');
    redirect('/');
}

// Prevent cross-tenant checkout: a token only processes on its own tenant's host
if ($link['tenant_id'] !== currentTenantId()) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$tenantId = $link['tenant_id'];
$product = Product::find($link['product_id'], $tenantId);
if (!$product || $product['status'] !== 'published') {
    flashMessage('error', 'Product not available.');
    redirect('/');
}

$name = sanitize($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$company = sanitize($_POST['company'] ?? '');
$paymentMethod = sanitize($_POST['payment_method'] ?? 'invoice');
$quantity = max(1, (int)($_POST['quantity'] ?? 1));
$notes = sanitize($_POST['notes'] ?? '');

$errors = [];
if (empty($name)) $errors[] = 'Name is required.';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';

if (!empty($errors)) {
    flashMessage('error', implode(' ', $errors));
    redirect('/pay/' . $token);
}

$price = (float)($link['custom_price_dkk'] ?? $product['price_dkk']);
$productName = $link['custom_name'] ?? $product['name'];
$subtotal = $price * $quantity;
$taxRate = 0.25;
$tax = round($subtotal * $taxRate, 2);
$total = round($subtotal + $tax, 2);

$orderNumber = 'KZ-' . strtoupper(substr(md5($tenantId . time()), 0, 8));
$customerId = isAuthenticated() && isCustomer() ? currentUserId() : null;

$paymentReference = null;
$status = 'pending';

if ($paymentMethod === 'card') {
    try {
        $stripe = new StripeService(null, $tenantId);
        if ($stripe->isConfigured()) {
            $amountInOerer = (int)round($total * 100);
            $paymentIntent = $stripe->createPaymentIntent($amountInOerer, 'dkk', [
                'tenant_id' => $tenantId,
                'order_number' => $orderNumber,
                'payment_link_id' => $link['id'],
            ]);
            $paymentReference = $paymentIntent['id'];
            $status = 'awaiting_payment';
        } else {
            $paymentMethod = 'invoice';
        }
    } catch (Exception $e) {
        if (APP_DEBUG) {
            error_log('Stripe payment intent failed: ' . $e->getMessage());
        }
        flashMessage('error', 'Payment error. Please try again.');
        redirect('/pay/' . $token);
    }
}

$billingAddress = json_encode([
    'name' => $name,
    'company' => $company,
]);

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

OrderItem::create([
    'order_id' => $orderId,
    'product_id' => $product['id'],
    'product_name' => $productName,
    'product_sku' => $product['sku'] ?? null,
    'quantity' => $quantity,
    'unit_price_dkk' => $price,
    'total_price_dkk' => $subtotal,
    'is_digital' => $product['is_digital'] ?? 0,
    'digital_file_path' => $product['digital_file_path'] ?? null,
]);

// Update payment link usage
PaymentLink::incrementUsed($link['id']);

// Update order with payment link reference
Order::update($orderId, ['payment_link_id' => $link['id']]);

// Send confirmation email
try {
    $emailService = EmailServiceFactory::create();
    if ($emailService->isConfigured()) {
        $subject = 'Order Confirmation: ' . $orderNumber;
        $htmlContent = '<h2>Thank you for your order!</h2>';
        $htmlContent .= '<p>Hi ' . h($name) . ',</p>';
        $htmlContent .= '<p>We have received your order <strong>' . h($orderNumber) . '</strong>.</p>';
        $htmlContent .= '<p>Total: ' . formatMoney($total) . ' (incl. tax)</p>';
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
    'payment_link_id' => $link['id'],
]);

if ($paymentMethod === 'card' && $paymentReference) {
    $_SESSION['pending_order_id'] = $orderId;
    $_SESSION['stripe_client_secret'] = $paymentIntent['client_secret'] ?? null;
    redirect('/checkout/betaling');
}

$redirectUrl = $link['redirect_url'] ?: '/konto/ordrer/' . $orderId;
flashMessage('success', 'Your order has been placed! You will receive a confirmation email.');
redirect($redirectUrl);
