<?php

use App\Models\Ebook;
use App\Models\Tenant;
use App\Models\EbookPurchase;
use App\Models\Setting;
use App\Services\StripeService;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$ebookId = (int)($input['ebook_id'] ?? 0);
$tenantSlug = $input['tenant_slug'] ?? null;

if (!$ebookId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing ebook_id']);
    exit;
}

$ebook = Ebook::find($ebookId);
if (!$ebook || $ebook['status'] !== 'published' || $ebook['price_dkk'] <= 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Ebook not found or not for sale']);
    exit;
}

// Get tenant from subdomain or slug
$tenant = null;
if ($tenantSlug) {
    $tenant = Tenant::findBySlug($tenantSlug);
}
if (!$tenant) {
    // Try subdomain
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $platformDomain = PLATFORM_DOMAIN;
    if (str_ends_with($host, '.' . $platformDomain)) {
        $subdomain = str_replace('.' . $platformDomain, '', $host);
        $tenant = Tenant::findBySlug($subdomain);
    }
}

if (!$tenant || !$tenant['stripe_connect_id'] || !$tenant['stripe_connect_charges_enabled']) {
    http_response_code(400);
    echo json_encode(['error' => 'Betalinger er ikke konfigureret for denne side']);
    exit;
}

// Calculate fee
$feePercent = (float)(Setting::get('stripe_application_fee_percent', 10));
$amountCents = (int)round($ebook['price_dkk'] * 100);
$feeCents = (int)round($amountCents * ($feePercent / 100));

try {
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

    $session = StripeService::createEbookCheckoutSession(
        $ebook['title'],
        $amountCents,
        'dkk',
        $feeCents,
        $tenant['stripe_connect_id'],
        $baseUrl . '/ebog/kob-succes/{CHECKOUT_SESSION_ID}',
        $baseUrl . '/ebog/' . $ebook['slug'],
        [
            'ebook_id' => $ebook['id'],
            'tenant_id' => $tenant['id'],
        ]
    );

    // Create purchase record
    EbookPurchase::create([
        'tenant_id' => $tenant['id'],
        'ebook_id' => $ebook['id'],
        'stripe_checkout_session_id' => $session->id,
        'amount_cents' => $amountCents,
        'currency' => 'dkk',
        'application_fee_cents' => $feeCents,
        'status' => 'pending',
    ]);

    echo json_encode(['checkout_url' => $session->url]);
} catch (\Exception $e) {
    error_log("Ebook checkout error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Kunne ikke oprette betaling']);
}
exit;
