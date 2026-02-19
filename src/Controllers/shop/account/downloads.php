<?php

use App\Auth\Auth;
use App\Database\Database;

Auth::requireCustomer();

$userId = Auth::id();
$tenantId = currentTenantId();

// Get digital order items for this customer
$db = Database::getConnection();
$stmt = $db->prepare("
    SELECT oi.name, oi.created_at, p.digital_file_path, p.slug as product_slug
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    LEFT JOIN products p ON p.id = oi.product_id
    WHERE o.customer_id = ? AND o.tenant_id = ? AND o.payment_status = 'paid' AND p.is_digital = 1
    ORDER BY oi.created_at DESC
");
$stmt->execute([$userId, $tenantId]);
$downloads = $stmt->fetchAll();

// Add download URLs
foreach ($downloads as &$download) {
    if ($download['digital_file_path']) {
        $download['download_url'] = '/lp/download/' . bin2hex(random_bytes(16)); // Simplified
    } else {
        $download['download_url'] = null;
    }
}

view('shop/account/downloads', ['downloads' => $downloads]);
