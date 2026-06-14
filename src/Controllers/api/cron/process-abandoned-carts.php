<?php

use App\Models\AbandonedCart;
use App\Services\EmailServiceFactory;
use App\Database\Database;

header('Content-Type: application/json');

// Simple cron key auth
$cronKey = $_GET['key'] ?? '';
if (defined('CRON_SECRET_KEY') && $cronKey !== CRON_SECRET_KEY) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db = Database::getConnection();

// Get all tenants (full row so EmailServiceFactory can read each tenant's email config)
$stmt = $db->prepare("SELECT * FROM tenants WHERE status = 'active'");
$stmt->execute();
$tenants = $stmt->fetchAll();

$processed = 0;
$errors = 0;

foreach ($tenants as $tenant) {
    // Mark carts as abandoned if checkout started >30 min ago with no completion
    $stmt = $db->prepare("
        UPDATE abandoned_carts
        SET abandoned_at = NOW()
        WHERE tenant_id = ?
          AND status = 'active'
          AND abandoned_at IS NULL
          AND checkout_started_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE)
    ");
    $stmt->execute([$tenant['id']]);

    // Expire old carts
    AbandonedCart::expireOld($tenant['id'], 30);

    // Get carts ready for recovery emails
    // 1st email: 1 hour after abandonment
    // 2nd email: 24 hours after abandonment
    // 3rd email: 72 hours after abandonment
    $delays = [60, 1440, 4320]; // minutes

    $carts = AbandonedCart::getPendingRecovery($tenant['id'], 60, 3);

    foreach ($carts as $cart) {
        $emailsSent = (int)$cart['emails_sent'];
        $requiredDelay = $delays[$emailsSent] ?? 4320;

        // Check if enough time has passed for this email number
        $abandonedAt = strtotime($cart['abandoned_at']);
        $minutesSinceAbandoned = (time() - $abandonedAt) / 60;

        if ($minutesSinceAbandoned < $requiredDelay) {
            continue;
        }

        try {
            $emailService = EmailServiceFactory::create($tenant);
            if (!$emailService->isConfigured()) continue;

            $items = json_decode($cart['cart_data'], true) ?: [];
            $customerName = $cart['customer_name'] ?: 'there';

            $subject = match($emailsSent) {
                0 => 'You left something behind!',
                1 => 'Your cart is waiting for you',
                2 => 'Last chance: Complete your purchase',
                default => 'Complete your purchase',
            };

            $htmlContent = '<h2>Hi ' . htmlspecialchars($customerName) . ',</h2>';

            if ($emailsSent === 0) {
                $htmlContent .= '<p>It looks like you started a checkout but didn\'t finish. Your items are still waiting for you!</p>';
            } elseif ($emailsSent === 1) {
                $htmlContent .= '<p>We noticed you haven\'t completed your purchase yet. Don\'t miss out!</p>';
            } else {
                $htmlContent .= '<p>This is your last reminder. Your cart items won\'t be reserved much longer.</p>';
            }

            $htmlContent .= '<h3>Your Cart:</h3><ul>';
            foreach ($items as $item) {
                $itemName = $item['name'] ?? 'Product';
                $itemQty = $item['quantity'] ?? 1;
                $itemPrice = $item['price'] ?? 0;
                $htmlContent .= '<li>' . htmlspecialchars($itemName) . ' x' . $itemQty . ' - ' . number_format($itemPrice * $itemQty, 2, ',', '.') . ' DKK</li>';
            }
            $htmlContent .= '</ul>';
            $htmlContent .= '<p><strong>Total: ' . number_format($cart['subtotal_dkk'], 2, ',', '.') . ' DKK</strong></p>';
            $htmlContent .= '<p><a href="' . htmlspecialchars(url('/checkout')) . '" style="display:inline-block;padding:12px 24px;background:#4F46E5;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">Complete Your Purchase</a></p>';

            $emailService->sendTransactionalEmail($cart['email'], $subject, $htmlContent);
            AbandonedCart::markEmailSent($cart['id']);
            $processed++;

        } catch (Exception $e) {
            $errors++;
            if (APP_DEBUG) {
                error_log("Abandoned cart email failed for cart #{$cart['id']}: " . $e->getMessage());
            }
        }
    }
}

echo json_encode([
    'success' => true,
    'processed' => $processed,
    'errors' => $errors,
]);
