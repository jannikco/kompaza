<?php
/**
 * Guest-friendly buy for imported JH sales pages.
 * POST /{office-os|creator-os|founder-os}/buy|buy-plan
 * Supports multi-currency + 3-installment plan (Stripe subscription).
 */

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use App\Models\User;
use App\Services\StripeService;
use App\Auth\Auth;

$tenant = currentTenant();
$tenantId = currentTenantId();
$product = $dynamicParams['product'] ?? ($_GET['product'] ?? '');
$product = preg_replace('/[^a-z0-9\-]/', '', strtolower($product));
$planMode = !empty($dynamicParams['plan']) || str_ends_with($_SERVER['REQUEST_URI'] ?? '', '/buy-plan')
    || (($_POST['sku'] ?? '') === 'plan');

$allowed = ['office-os', 'creator-os', 'founder-os'];
if (!in_array($product, $allowed, true)) {
    flashMessage('error', 'Unknown product.');
    redirect('/');
}

if (!isPost()) {
    redirect('/' . $product);
}

$ip = getClientIp();
if (!checkRateLimit($ip, 'track_buy_' . $product, 20, 3600)) {
    flashMessage('error', 'Too many checkout attempts. Please try again later.');
    redirect('/' . $product);
}

$course = Course::findBySlug($product, $tenantId);
if (!$course || $course['status'] !== 'published') {
    flashMessage('error', 'This course is not available yet.');
    redirect('/courses');
}

$email = trim($_POST['email'] ?? $_POST['customer_email'] ?? '');
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $email = '';
}

$currency = visitorCurrency();
$sku = $planMode ? 'plan' : 'full';
$amountMajor = jhTrackAmount($product, $sku, $currency);
// Fallback to course DKK if catalog missing
if ($amountMajor <= 0) {
    $amountMajor = (float)$course['price_dkk'];
    $currency = 'dkk';
}
$amountCents = (int)round($amountMajor * 100);
if ($amountCents < 50) {
    flashMessage('error', 'Invalid price configuration.');
    redirect('/' . $product);
}

$tracks = jhTrackPrices();
$name = ($currency === 'dkk' ? ($tracks[$product]['name'] ?? $course['title']) : ($tracks[$product]['name_en'] ?? $course['title']));
if ($planMode) {
    $name .= ' — 3 monthly installments';
}

try {
    $stripe = new StripeService(null, $tenantId);
    if (!$stripe->isConfigured()) {
        $stripe = new StripeService(defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : null);
    }
    if (!$stripe->isConfigured()) {
        flashMessage('error', 'Payments are not configured yet.');
        redirect('/' . $product);
    }

    $base = 'https://' . ($tenant['slug'] ?? 'jannikhansen') . '.' . PLATFORM_DOMAIN;
    $meta = [
        'type' => 'course_purchase',
        'tenant_id' => $tenantId,
        'course_id' => $course['id'],
        'course_slug' => $course['slug'],
        'sku' => $sku,
        'currency' => $currency,
    ];

    if ($planMode) {
        $session = $stripe->createInstallmentCheckoutSession(
            $name,
            $amountCents,
            $currency,
            $base . '/purchase/success?session_id={CHECKOUT_SESSION_ID}',
            $base . '/' . $product,
            $meta,
            $email ?: null,
            3
        );
    } else {
        $session = $stripe->createOneTimeCheckoutSession(
            $name,
            $amountCents,
            $currency,
            $base . '/purchase/success?session_id={CHECKOUT_SESSION_ID}',
            $base . '/' . $product,
            $meta,
            $email ?: null
        );
    }

    $url = $session['url'] ?? null;
    if (!$url) {
        throw new \Exception('Checkout session missing URL');
    }
    header('Location: ' . $url);
    exit;
} catch (\Exception $e) {
    error_log('track-buy error: ' . $e->getMessage());
    flashMessage('error', 'Could not start checkout. Please try again.');
    redirect('/' . $product);
}
