<?php

use App\Models\AbandonedCart;

header('Content-Type: application/json');

$tenantId = currentTenantId();
$input = json_decode(file_get_contents('php://input'), true);

$email = trim($input['email'] ?? '');
$name = trim($input['name'] ?? '');

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email']);
    exit;
}

// Get cart from session
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    echo json_encode(['success' => false, 'error' => 'Cart is empty']);
    exit;
}

// Calculate subtotal
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 1);
}

$sessionId = session_id();
$customerId = isAuthenticated() && isCustomer() ? currentUserId() : null;

// Check for existing abandoned cart for this session
$existing = AbandonedCart::findBySession($sessionId, $tenantId);

if ($existing) {
    AbandonedCart::update($existing['id'], [
        'email' => $email,
        'customer_name' => $name ?: $existing['customer_name'],
        'cart_data' => json_encode($cart),
        'subtotal_dkk' => $subtotal,
        'customer_id' => $customerId ?? $existing['customer_id'],
    ]);
} else {
    AbandonedCart::create([
        'tenant_id' => $tenantId,
        'session_id' => $sessionId,
        'customer_id' => $customerId,
        'email' => $email,
        'customer_name' => $name,
        'cart_data' => json_encode($cart),
        'subtotal_dkk' => $subtotal,
    ]);
}

echo json_encode(['success' => true]);
