<?php
/**
 * Stripe Checkout success handler for track/library one-time purchases.
 * GET /purchase/success?session_id=cs_...
 */

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use App\Models\Ebook;
use App\Models\EbookPurchase;
use App\Models\User;
use App\Models\Order;
use App\Services\StripeService;
use App\Auth\Auth;
use App\Database\Database;

$tenant = currentTenant();
$tenantId = currentTenantId();
$sessionId = $_GET['session_id'] ?? '';

if ($sessionId === '') {
    flashMessage('error', 'Missing payment session.');
    redirect('/');
}

try {
    $stripe = new StripeService(null, $tenantId);
    if (!$stripe->isConfigured()) {
        $stripe = new StripeService(defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : null);
    }
    $session = $stripe->retrieveCheckoutSession($sessionId);
} catch (\Exception $e) {
    error_log('purchase-success retrieve: ' . $e->getMessage());
    flashMessage('error', 'Could not verify payment.');
    redirect('/');
}

$paid = ($session['payment_status'] ?? '') === 'paid' || ($session['status'] ?? '') === 'complete';
if (!$paid) {
    flashMessage('error', 'Payment not completed.');
    redirect('/');
}

$meta = $session['metadata'] ?? [];
$type = $meta['type'] ?? '';
$email = $session['customer_details']['email'] ?? $session['customer_email'] ?? null;
$name = $session['customer_details']['name'] ?? 'Customer';

// Ensure customer user
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
    if ($user) Auth::login($user);
}

if ($type === 'course_purchase') {
    $courseId = (int)($meta['course_id'] ?? 0);
    $course = $courseId ? Course::find($courseId, $tenantId) : Course::findBySlug($meta['course_slug'] ?? '', $tenantId);
    if ($course && $userId) {
        $existing = CourseEnrollment::findByUserAndCourse($userId, $course['id']);
        if (!$existing) {
            $totalLessons = 0;
            try { $totalLessons = (int)CourseLesson::countByCourse($course['id']); } catch (\Exception $e) {}
            CourseEnrollment::create([
                'tenant_id' => $tenantId,
                'course_id' => $course['id'],
                'user_id' => $userId,
                'enrollment_source' => 'purchase',
                'status' => 'active',
                'total_lessons' => $totalLessons,
            ]);
        }
        // Record order for sales dashboards
        try {
            $db = Database::getConnection();
            $orderNumber = 'KZ-' . strtoupper(substr(md5($sessionId), 0, 8));
            $amount = ((int)($session['amount_total'] ?? 0)) / 100;
            $stmt = $db->prepare("
                INSERT INTO orders (tenant_id, order_number, customer_id, customer_email, customer_name, status, subtotal_dkk, tax_dkk, total_dkk, payment_method, payment_reference)
                VALUES (?, ?, ?, ?, ?, 'completed', ?, 0, ?, 'stripe', ?)
            ");
            $stmt->execute([$tenantId, $orderNumber, $userId, $email, $name, $amount, $amount, $sessionId]);
        } catch (\Exception $e) {
            error_log('order record: ' . $e->getMessage());
        }
        flashMessage('success', 'Payment received — you are enrolled in ' . $course['title'] . '!');
        redirect('/course/' . $course['slug'] . '/learn');
    }
}

if ($type === 'ebook_purchase') {
    $ebookId = (int)($meta['ebook_id'] ?? 0);
    $ebook = $ebookId ? Ebook::find($ebookId, $tenantId) : Ebook::findBySlug($meta['ebook_slug'] ?? '', $tenantId);
    if ($ebook) {
        try {
            $db = Database::getConnection();
            try {
                $db->prepare("
                    UPDATE ebook_purchases SET status='completed', customer_email=COALESCE(?, customer_email),
                    customer_name=COALESCE(?, customer_name), completed_at=NOW()
                    WHERE stripe_checkout_session_id=? OR (ebook_id=? AND status='pending' AND tenant_id=?)
                ")->execute([$email, $name, $sessionId, $ebook['id'], $tenantId]);
            } catch (\Exception $e) { /* table optional */ }

            // Bundle: grant download tokens for every published ebook on the tenant
            if (($meta['is_bundle'] ?? '') === '1' || ($ebook['slug'] ?? '') === 'everything-bundle') {
                $books = $db->prepare("SELECT id, slug FROM ebooks WHERE tenant_id=? AND status='published' AND price_dkk > 0");
                $books->execute([$tenantId]);
                $firstToken = null;
                foreach ($books->fetchAll() as $b) {
                    $tok = bin2hex(random_bytes(16));
                    $db->prepare("
                        INSERT INTO download_tokens (tenant_id, token, source_type, source_id, email, max_downloads, expires_at)
                        VALUES (?, ?, 'ebook', ?, ?, 20, DATE_ADD(NOW(), INTERVAL 365 DAY))
                    ")->execute([$tenantId, $tok, $b['id'], $email]);
                    if (!$firstToken) $firstToken = $tok;
                }
                flashMessage('success', 'Payment received! Your complete library is unlocked.');
                redirect($firstToken ? '/ebog/download/' . $firstToken : '/eboger');
            }

            $token = bin2hex(random_bytes(24));
            $db->prepare("
                INSERT INTO download_tokens (tenant_id, token, source_type, source_id, email, max_downloads, expires_at)
                VALUES (?, ?, 'ebook', ?, ?, 10, DATE_ADD(NOW(), INTERVAL 90 DAY))
            ")->execute([$tenantId, $token, $ebook['id'], $email]);

            flashMessage('success', 'Payment received! Download your book below.');
            redirect('/ebog/download/' . $token);
        } catch (\Exception $e) {
            error_log('ebook fulfill: ' . $e->getMessage());
            flashMessage('success', 'Payment received for ' . $ebook['title'] . '.');
            redirect('/ebog/' . $ebook['slug']);
        }
    }
}

// Subscription installment plans also return mode=subscription
if (($session['mode'] ?? '') === 'subscription' && empty($type)) {
    $type = $meta['type'] ?? 'course_purchase';
}

flashMessage('success', 'Payment received. Thank you!');
redirect('/konto');
