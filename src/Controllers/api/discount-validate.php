<?php

use App\Models\DiscountCode;

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$code = trim($input['code'] ?? '');
$subtotal = (float)($input['subtotal'] ?? 0);

$tenantId = currentTenantId();

if (empty($code)) {
    echo json_encode(['valid' => false, 'error' => 'Please enter a discount code.']);
    exit;
}

$result = DiscountCode::validate($code, $tenantId, $subtotal);

if (!$result['valid']) {
    echo json_encode([
        'valid' => false,
        'discount_amount' => 0,
        'error' => $result['error'],
        'label' => null,
    ]);
    exit;
}

$discount = $result['discount'];
if ($discount['type'] === 'percentage') {
    $label = rtrim(rtrim(number_format($discount['value'], 2), '0'), '.') . '% OFF';
} else {
    $label = number_format($discount['value'], 2) . ' DKK OFF';
}

echo json_encode([
    'valid' => true,
    'discount_amount' => $result['discount_amount'],
    'error' => null,
    'label' => $label,
]);
