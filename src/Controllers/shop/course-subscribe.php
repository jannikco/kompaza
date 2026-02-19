<?php

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Auth\Auth;

Auth::requireCustomer();

$tenant = currentTenant();
$tenantId = currentTenantId();
$userId = currentUserId();
$user = currentUser();

$course = Course::findBySlug($slug, $tenantId);
if (!$course || $course['pricing_type'] !== 'subscription') {
    flashMessage('error', 'Course not found or not available for subscription.');
    redirect('/courses');
}

$existing = CourseEnrollment::findByUserAndCourse($userId, $course['id']);
if ($existing) {
    redirect('/course/' . $course['slug'] . '/learn');
}

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/course/' . $course['slug']);
}

$plan = sanitize($_POST['plan'] ?? 'monthly');
$stripePriceId = $plan === 'yearly' ? $course['stripe_yearly_price_id'] : $course['stripe_monthly_price_id'];

if (!$stripePriceId) {
    flashMessage('error', 'Subscription pricing not configured for this course.');
    redirect('/course/' . $course['slug']);
}

$stripeKey = $tenant['stripe_secret_key'] ?? STRIPE_SECRET_KEY;
if (!$stripeKey) {
    flashMessage('error', 'Payment not configured.');
    redirect('/course/' . $course['slug']);
}

// Create or retrieve Stripe Customer
$ch = curl_init('https://api.stripe.com/v1/customers');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_USERPWD => $stripeKey . ':',
    CURLOPT_POSTFIELDS => http_build_query([
        'email' => $user['email'],
        'name' => $user['name'],
        'metadata[user_id]' => $userId,
        'metadata[tenant_id]' => $tenantId,
    ]),
]);
$customer = json_decode(curl_exec($ch), true);
curl_close($ch);

if (empty($customer['id'])) {
    flashMessage('error', 'Could not create payment customer.');
    redirect('/course/' . $course['slug']);
}

// Create Checkout Session for subscription
$ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_USERPWD => $stripeKey . ':',
    CURLOPT_POSTFIELDS => http_build_query([
        'customer' => $customer['id'],
        'mode' => 'subscription',
        'line_items[0][price]' => $stripePriceId,
        'line_items[0][quantity]' => 1,
        'success_url' => url('course/' . $course['slug'] . '/learn') . '?subscribed=1',
        'cancel_url' => url('course/' . $course['slug']),
        'metadata[course_id]' => $course['id'],
        'metadata[tenant_id]' => $tenantId,
        'metadata[user_id]' => $userId,
        'metadata[type]' => 'course_subscription',
        'subscription_data[metadata][course_id]' => $course['id'],
        'subscription_data[metadata][user_id]' => $userId,
        'subscription_data[metadata][tenant_id]' => $tenantId,
    ]),
]);
$session = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!empty($session['url'])) {
    redirect($session['url']);
} else {
    flashMessage('error', 'Could not create subscription session.');
    redirect('/course/' . $course['slug']);
}
