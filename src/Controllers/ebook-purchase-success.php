<?php

use App\Models\EbookPurchase;
use App\Models\Ebook;
use App\Database\Database;

$checkoutSessionId = $session_id ?? null;

$purchase = null;
$ebook = null;
$downloadUrl = null;

if ($checkoutSessionId) {
    $purchase = EbookPurchase::findByCheckoutSession($checkoutSessionId);
    if ($purchase) {
        $ebook = Ebook::find($purchase['ebook_id']);
        if ($purchase['download_token_id']) {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT token FROM download_tokens WHERE id = ?");
            $stmt->execute([$purchase['download_token_id']]);
            $tokenRow = $stmt->fetch();
            if ($tokenRow) {
                $downloadUrl = '/ebog/download/' . $tokenRow['token'];
            }
        }
    }
}

$pageTitle = 'Tak for dit køb';
$metaDescription = 'Tak for dit køb';

ob_start();
include VIEWS_PATH . '/ebook-purchase-success.php';
$content = ob_get_clean();

include VIEWS_PATH . '/layout.php';
