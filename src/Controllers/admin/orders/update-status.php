<?php

use App\Models\Order;
use App\Database\Database;

if (!isPost()) redirect('/admin/ordrer');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/ordrer');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/ordrer');

$order = Order::find($id, $tenantId);
if (!$order) {
    flashMessage('error', 'Ordre ikke fundet.');
    redirect('/admin/ordrer');
}

$status = sanitize($_POST['status'] ?? '');
$note = sanitize($_POST['note'] ?? '');

$validStatuses = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
if (!in_array($status, $validStatuses)) {
    flashMessage('error', 'Ugyldig status.');
    redirect('/admin/ordrer/vis?id=' . $id);
}

Order::updateStatus($id, $status, $note ?: null);

// If shipped, update tracking info
if ($status === 'shipped') {
    $updateData = ['shipped_at' => date('Y-m-d H:i:s')];

    $trackingNumber = sanitize($_POST['tracking_number'] ?? '');
    $trackingUrl = sanitize($_POST['tracking_url'] ?? '');

    if ($trackingNumber) {
        $updateData['tracking_number'] = $trackingNumber;
    }
    if ($trackingUrl) {
        $updateData['tracking_url'] = $trackingUrl;
    }

    $db = Database::getConnection();
    $fields = [];
    $values = [];
    foreach ($updateData as $key => $value) {
        $fields[] = "$key = ?";
        $values[] = $value;
    }
    $values[] = $id;
    $stmt = $db->prepare("UPDATE orders SET " . implode(', ', $fields) . " WHERE id = ?");
    $stmt->execute($values);
}

// If delivered, update delivered_at
if ($status === 'delivered') {
    $db = Database::getConnection();
    $stmt = $db->prepare("UPDATE orders SET delivered_at = ? WHERE id = ?");
    $stmt->execute([date('Y-m-d H:i:s'), $id]);
}

logAudit('order_status_updated', 'order', $id, ['status' => $status]);
flashMessage('success', 'Ordrestatus opdateret.');
redirect('/admin/ordrer/vis?id=' . $id);
