<?php

use App\Models\Product;

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$productId = (int)($input['product_id'] ?? 0);
$quantity = max(1, (int)($input['quantity'] ?? 1));

if (!$productId) {
    echo json_encode(['success' => false, 'error' => 'Product ID required']);
    exit;
}

$product = Product::find($productId, currentTenantId());
if (!$product || $product['status'] !== 'published') {
    echo json_encode(['success' => false, 'error' => 'Product not found']);
    exit;
}

// Check stock
if ($product['track_stock'] && $product['stock_quantity'] < $quantity) {
    echo json_encode(['success' => false, 'error' => 'Insufficient stock']);
    exit;
}

// Get or create cart in session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$found = false;
foreach ($_SESSION['cart'] as &$item) {
    if ($item['product_id'] == $productId) {
        $item['quantity'] += $quantity;
        $found = true;
        break;
    }
}

if (!$found) {
    $_SESSION['cart'][] = [
        'product_id' => $productId,
        'name' => $product['name'],
        'price' => (float)$product['price_dkk'],
        'quantity' => $quantity,
        'image' => $product['image_path'],
    ];
}

$cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
echo json_encode(['success' => true, 'cart_count' => $cartCount]);
