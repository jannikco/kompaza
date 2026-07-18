<?php
/**
 * Guest-friendly buy endpoint for imported JH sales pages.
 * POST /{office-os|creator-os|founder-os}/buy
 * Maps to published course with same slug and starts Stripe Checkout.
 */

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use App\Models\User;
use App\Services\StripeService;
use App\Database\Database;
use App\Auth\Auth;

$tenant = currentTenant();
$tenantId = currentTenantId();
$product = $dynamicParams['product'] ?? ($_GET['product'] ?? '');
$product = preg_replace('/[^a-z0-9\-]/', '', strtolower($product));

$allowed = ['office-os', 'creator-os', 'founder-os'];
if (!in_array($product, $allowed, true)) {
    flashMessage('error', 'Unknown product.');
    redirect('/');
}

if (!isPost()) {
    redirect('/' . $product);
}

// Soft CSRF: accept missing token from imported static HTML, but rate-limit
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
$name = trim($_POST['name'] ?? $_POST['customer_name'] ?? 'Customer');
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Guest may not have posted email — Stripe Checkout will collect it
    $email = null;
}

// Free course: enroll immediately
if (($course['pricing_type'] ?? '') === 'free' || (float)($course['price_dkk'] ?? 0) <= 0) {
    $userId = currentUserId();
    if (!$userId && $email) {
        $existing = User::findByEmail($email, $tenantId);
        if ($existing) {
            $userId = (int)$existing['id'];
        } else {
            $userId = (int)User::create([
                'tenant_id' => $tenantId,
                'role' => 'customer',
                'name' => $name,
                'email' => $email,
                'password' => bin2hex(random_bytes(8)),
                'status' => 'active',
            ]);
        }
        $user = User::find($userId);
        Auth::login($user);
    }
    if ($userId) {
        $existing = CourseEnrollment::findByUserAndCourse($userId, $course['id']);
        if (!$existing) {
            $totalLessons = CourseLesson::countByCourse($course['id']) ?? 0;
            CourseEnrollment::create([
                'tenant_id' => $tenantId,
                'course_id' => $course['id'],
                'user_id' => $userId,
                'enrollment_source' => 'free',
                'status' => 'active',
                'total_lessons' => $totalLessons,
            ]);
        }
        flashMessage('success', 'You are enrolled! Start learning.');
        redirect('/course/' . $course['slug'] . '/learn');
    }
    flashMessage('error', 'Please log in or provide an email to enroll.');
    redirect('/login?redirect=' . urlencode('/course/' . $course['slug']));
}

$priceDkk = (float)$course['price_dkk'];
$amountCents = (int)round($priceDkk * 100);
if ($amountCents < 50) {
    flashMessage('error', 'Invalid price configuration.');
    redirect('/' . $product);
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
    $session = $stripe->createOneTimeCheckoutSession(
        $course['title'],
        $amountCents,
        $tenant['currency'] ?? 'dkk',
        $base . '/purchase/success?session_id={CHECKOUT_SESSION_ID}',
        $base . '/' . $product,
        [
            'type' => 'course_purchase',
            'tenant_id' => $tenantId,
            'course_id' => $course['id'],
            'course_slug' => $course['slug'],
        ],
        $email
    );

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
