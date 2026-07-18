<?php

use App\Services\StripeService;
use App\Models\EbookPurchase;
use App\Models\Ebook;
use App\Models\Tenant;
use App\Database\Database;

// Stripe sends raw JSON
$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $stripe = new StripeService(defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : null);
    $secret = defined('STRIPE_CONNECT_WEBHOOK_SECRET') ? STRIPE_CONNECT_WEBHOOK_SECRET : null;
    $event = $stripe->constructWebhookEvent($payload, $sigHeader, $secret);
} catch (\Exception $e) {
    error_log("Stripe Connect webhook signature failed: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

header('Content-Type: application/json');

$eventType = $event['type'] ?? '';
$object = $event['data']['object'] ?? [];

switch ($eventType) {
    case 'checkout.session.completed':
        if (($object['mode'] ?? '') === 'payment') {
            $purchase = EbookPurchase::findByCheckoutSession($object['id'] ?? '');
            if ($purchase) {
                // Generate download token
                $db = Database::getConnection();
                $downloadToken = bin2hex(random_bytes(32));
                $stmt = $db->prepare("
                    INSERT INTO download_tokens (token, source_type, source_id, max_downloads, expires_at)
                    VALUES (?, 'ebook', ?, 5, DATE_ADD(NOW(), INTERVAL 7 DAY))
                ");
                $stmt->execute([$downloadToken, $purchase['ebook_id']]);
                $tokenId = $db->lastInsertId();

                // Update purchase
                EbookPurchase::update($purchase['id'], [
                    'customer_email' => $object['customer_details']['email'] ?? null,
                    'customer_name' => $object['customer_details']['name'] ?? null,
                    'stripe_payment_intent_id' => $object['payment_intent'] ?? null,
                    'status' => 'completed',
                    'download_token_id' => $tokenId,
                    'completed_at' => date('Y-m-d H:i:s'),
                ]);

                // Increment ebook download count
                Ebook::incrementDownloads($purchase['ebook_id']);
            }
        }
        break;

    case 'account.updated':
        $accountId = $object['id'] ?? '';
        $tenant = $accountId ? Tenant::findByStripeConnectId($accountId) : null;
        if ($tenant) {
            Tenant::updateStripeConnect($tenant['id'], [
                'stripe_connect_onboarded' => !empty($object['details_submitted']) ? 1 : 0,
                'stripe_connect_charges_enabled' => !empty($object['charges_enabled']) ? 1 : 0,
                'stripe_connect_payouts_enabled' => !empty($object['payouts_enabled']) ? 1 : 0,
            ]);
        }
        break;
}

echo json_encode(['received' => true]);
exit;
