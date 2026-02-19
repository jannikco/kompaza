<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Database\Database;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$order = Order::find($id, $tenantId);

if (!$order) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$items = OrderItem::allByOrder($id);

// Load status history
$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at DESC");
$stmt->execute([$id]);
$statusHistory = $stmt->fetchAll();

view('admin/orders/show', [
    'tenant' => currentTenant(),
    'order' => $order,
    'items' => $items,
    'statusHistory' => $statusHistory,
]);
