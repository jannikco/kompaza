<?php

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use App\Auth\Auth;
use App\Database\Database;

Auth::requireCustomer();

$tenant = currentTenant();
$tenantId = currentTenantId();
$userId = currentUserId();
$user = currentUser();

$course = Course::findBySlug($slug, $tenantId);
if (!$course || $course['pricing_type'] !== 'one_time') {
    flashMessage('error', 'Course not found or not available for purchase.');
    redirect('/courses');
}

// Check if already enrolled
$existing = CourseEnrollment::findByUserAndCourse($userId, $course['id']);
if ($existing) {
    redirect('/course/' . $course['slug'] . '/learn');
}

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/course/' . $course['slug']);
}

$db = Database::getConnection();

// Create order
$orderNumber = 'KZ-' . strtoupper(bin2hex(random_bytes(4)));
$priceDkk = (float)$course['price_dkk'];
$taxRate = (float)($tenant['tax_rate'] ?? 25);
$taxDkk = round($priceDkk * ($taxRate / (100 + $taxRate)), 2);
$subtotalDkk = $priceDkk - $taxDkk;

$stmt = $db->prepare("
    INSERT INTO orders (tenant_id, order_number, customer_id, status, customer_email, customer_name, subtotal_dkk, tax_dkk, total_dkk, payment_method)
    VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, 'stripe')
");
$stmt->execute([$tenantId, $orderNumber, $userId, $user['email'], $user['name'], $subtotalDkk, $taxDkk, $priceDkk]);
$orderId = $db->lastInsertId();

// Create order item
$stmt = $db->prepare("
    INSERT INTO order_items (order_id, item_type, course_id, name, quantity, unit_price_dkk, total_dkk)
    VALUES (?, 'course', ?, ?, 1, ?, ?)
");
$stmt->execute([$orderId, $course['id'], $course['title'], $priceDkk, $priceDkk]);

// Create Stripe PaymentIntent
$stripeKey = $tenant['stripe_secret_key'] ?? STRIPE_SECRET_KEY;
if (!$stripeKey) {
    flashMessage('error', 'Payment not configured. Please contact support.');
    redirect('/course/' . $course['slug']);
}

$amountOre = (int)round($priceDkk * 100);
$ch = curl_init('https://api.stripe.com/v1/payment_intents');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_USERPWD => $stripeKey . ':',
    CURLOPT_POSTFIELDS => http_build_query([
        'amount' => $amountOre,
        'currency' => strtolower($tenant['currency'] ?? 'dkk'),
        'metadata[order_id]' => $orderId,
        'metadata[course_id]' => $course['id'],
        'metadata[tenant_id]' => $tenantId,
        'metadata[type]' => 'course_purchase',
        'automatic_payment_methods[enabled]' => 'true',
    ]),
]);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (empty($response['client_secret'])) {
    flashMessage('error', 'Payment initialization failed.');
    redirect('/course/' . $course['slug']);
}

// Save payment intent ID
$stmt = $db->prepare("UPDATE orders SET stripe_payment_intent_id = ? WHERE id = ?");
$stmt->execute([$response['id'], $orderId]);

// Render checkout page
view('shop/course-checkout', [
    'tenant' => $tenant,
    'course' => $course,
    'order' => [
        'id' => $orderId,
        'order_number' => $orderNumber,
        'total_dkk' => $priceDkk,
    ],
    'clientSecret' => $response['client_secret'],
    'stripePublishableKey' => $tenant['stripe_publishable_key'] ?? STRIPE_PUBLISHABLE_KEY,
]);
