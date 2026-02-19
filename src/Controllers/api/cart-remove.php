<?php

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$productId = (int)($input['product_id'] ?? 0);

if (!$productId) {
    echo json_encode(['success' => false, 'error' => 'Product ID required']);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], fn($item) => $item['product_id'] != $productId));

$cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
echo json_encode(['success' => true, 'cart_count' => $cartCount]);
