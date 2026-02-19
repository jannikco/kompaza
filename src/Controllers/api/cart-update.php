<?php

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$productId = (int)($input['product_id'] ?? 0);
$quantity = (int)($input['quantity'] ?? 0);

if (!$productId) {
    echo json_encode(['success' => false, 'error' => 'Product ID required']);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($quantity <= 0) {
    // Remove item
    $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], fn($item) => $item['product_id'] != $productId));
} else {
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] == $productId) {
            $item['quantity'] = $quantity;
            break;
        }
    }
}

$cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
$subtotal = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $_SESSION['cart']));

echo json_encode(['success' => true, 'cart_count' => $cartCount, 'subtotal' => $subtotal]);
