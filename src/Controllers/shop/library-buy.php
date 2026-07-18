<?php
/**
 * Library book checkout for imported JH pages.
 * POST /bibliotek/{slug}/buy | /library/{slug}/buy | /ebog/{slug}/buy
 * Multi-currency; free books → download token; paid → Stripe; bundle grants all books.
 */

use App\Models\Ebook;
use App\Models\EbookPurchase;
use App\Models\EmailSignup;
use App\Services\StripeService;
use App\Services\EmailServiceFactory;
use App\Database\Database;

$tenant = currentTenant();
$tenantId = currentTenantId();
$slug = $dynamicParams['slug'] ?? ($_GET['slug'] ?? '');
$slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($slug));

if (!isPost()) {
    redirect('/bibliotek');
}

$ip = getClientIp();
if (!checkRateLimit($ip, 'library_buy', 30, 3600)) {
    flashMessage('error', 'Too many attempts. Please try again later.');
    redirect('/ebog/' . $slug);
}

$ebook = Ebook::findBySlug($slug, $tenantId);
if (!$ebook || $ebook['status'] !== 'published') {
    flashMessage('error', 'Book not found.');
    redirect('/eboger');
}

$email = trim($_POST['email'] ?? $_POST['customer_email'] ?? '');
$name = trim($_POST['name'] ?? $_POST['customer_name'] ?? 'Reader');
$currency = currentCurrency();

// Multi-currency: re-price from tier if we stored DKK as base
// For imported books price_dkk is DKK; convert using catalog ratios when currency != dkk
$priceDkk = (float)$ebook['price_dkk'];
if ($currency !== 'dkk' && $priceDkk > 0) {
    $tiers = jhLibraryTier();
    // Detect tier by matching DKK amount
    $tierKey = 'single';
    foreach ($tiers as $k => $row) {
        if (abs(($row['dkk'] ?? -1) - $priceDkk) < 0.01) {
            $tierKey = $k;
            break;
        }
    }
    $priceMajor = (float)($tiers[$tierKey][$currency] ?? $priceDkk);
} else {
    $priceMajor = $priceDkk;
}

// Free book → lead capture + download token
if ($priceMajor <= 0) {
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flashMessage('error', 'Please enter a valid email to get your free book.');
        redirect('/ebog/' . $slug);
    }

    try {
        EmailSignup::create([
            'tenant_id' => $tenantId,
            'email' => $email,
            'name' => $name,
            'source_type' => 'ebook',
            'source_id' => $ebook['id'],
            'source_slug' => $slug,
            'ip_address' => $ip,
        ]);
    } catch (\Exception $e) { /* ignore */ }

    $token = bin2hex(random_bytes(24));
    try {
        $db = Database::getConnection();
        $db->prepare("
            INSERT INTO download_tokens (tenant_id, token, source_type, source_id, email, max_downloads, expires_at)
            VALUES (?, ?, 'ebook', ?, ?, 10, DATE_ADD(NOW(), INTERVAL 30 DAY))
        ")->execute([$tenantId, $token, $ebook['id'], $email]);
    } catch (\Exception $e) {
        error_log('free book token: ' . $e->getMessage());
    }

    try {
        $svc = EmailServiceFactory::create($tenant);
        if ($svc && method_exists($svc, 'isConfigured') && $svc->isConfigured() && method_exists($svc, 'sendTransactionalEmail')) {
            $url = 'https://' . $tenant['slug'] . '.' . PLATFORM_DOMAIN . '/ebog/download/' . $token;
            $html = '<p>Hi ' . htmlspecialchars($name) . ',</p><p>Your free book <strong>' . htmlspecialchars($ebook['title']) . '</strong> is ready.</p><p><a href="' . htmlspecialchars($url) . '">Download PDF</a></p>';
            $svc->sendTransactionalEmail($email, 'Your free book: ' . $ebook['title'], $html);
        }
    } catch (\Exception $e) {
        error_log('free book email: ' . $e->getMessage());
    }

    flashMessage('success', 'Your free book is ready.');
    redirect('/ebog/download/' . $token);
}

$amountCents = (int)round($priceMajor * 100);

try {
    $stripe = new StripeService(null, $tenantId);
    if (!$stripe->isConfigured()) {
        $stripe = new StripeService(defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : null);
    }
    if (!$stripe->isConfigured()) {
        flashMessage('error', 'Payments are not configured yet.');
        redirect('/ebog/' . $slug);
    }

    $base = 'https://' . ($tenant['slug'] ?? 'jannikhansen') . '.' . PLATFORM_DOMAIN;
    $session = $stripe->createOneTimeCheckoutSession(
        $ebook['title'],
        $amountCents,
        $currency,
        $base . '/purchase/success?session_id={CHECKOUT_SESSION_ID}',
        $base . '/ebog/' . $slug,
        [
            'type' => 'ebook_purchase',
            'tenant_id' => $tenantId,
            'ebook_id' => $ebook['id'],
            'ebook_slug' => $ebook['slug'],
            'currency' => $currency,
            'is_bundle' => $slug === 'everything-bundle' ? '1' : '0',
        ],
        ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) ? $email : null
    );

    try {
        if (class_exists(EbookPurchase::class)) {
            EbookPurchase::create([
                'tenant_id' => $tenantId,
                'ebook_id' => $ebook['id'],
                'customer_email' => $email ?: null,
                'customer_name' => $name,
                'amount_cents' => $amountCents,
                'currency' => $currency,
                'status' => 'pending',
                'stripe_checkout_session_id' => $session['id'] ?? null,
            ]);
        }
    } catch (\Exception $e) {
        error_log('ebook purchase row: ' . $e->getMessage());
    }

    $url = $session['url'] ?? null;
    if (!$url) throw new \Exception('No checkout URL');
    header('Location: ' . $url);
    exit;
} catch (\Exception $e) {
    error_log('library-buy: ' . $e->getMessage());
    flashMessage('error', 'Could not start checkout. Please try again.');
    redirect('/ebog/' . $slug);
}
