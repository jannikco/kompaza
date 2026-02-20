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
    $event = StripeService::constructWebhookEvent($payload, $sigHeader, STRIPE_CONNECT_WEBHOOK_SECRET);
} catch (\Exception $e) {
    error_log("Stripe Connect webhook signature failed: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

header('Content-Type: application/json');

switch ($event->type) {
    case 'checkout.session.completed':
        $session = $event->data->object;
        if ($session->mode === 'payment') {
            $purchase = EbookPurchase::findByCheckoutSession($session->id);
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
                    'customer_email' => $session->customer_details->email ?? null,
                    'customer_name' => $session->customer_details->name ?? null,
                    'stripe_payment_intent_id' => $session->payment_intent,
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
        $account = $event->data->object;
        $tenant = Tenant::findByStripeConnectId($account->id);
        if ($tenant) {
            Tenant::updateStripeConnect($tenant['id'], [
                'stripe_connect_onboarded' => $account->details_submitted ? 1 : 0,
                'stripe_connect_charges_enabled' => $account->charges_enabled ? 1 : 0,
                'stripe_connect_payouts_enabled' => $account->payouts_enabled ? 1 : 0,
            ]);
        }
        break;
}

echo json_encode(['received' => true]);
exit;
