<?php

use App\Database\Database;
use App\Models\TenantSubscription;

if (!isPost()) {
    redirect('/billing/subscriptions');
}

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/billing/subscriptions');
}

$id     = (int) sanitize($_POST['id'] ?? 0);
$status = sanitize($_POST['status'] ?? '');

$allowedStatuses = ['trialing', 'active', 'past_due', 'unpaid', 'canceled', 'incomplete', 'paused'];
if (!in_array($status, $allowedStatuses, true)) {
    flashMessage('error', 'Invalid status.');
    redirect('/billing/subscriptions');
}

$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM tenant_subscriptions WHERE id = ?");
$stmt->execute([$id]);
$subscription = $stmt->fetch();

if (!$subscription) {
    flashMessage('error', 'Subscription not found.');
    redirect('/billing/subscriptions');
}

$update = ['status' => $status];

if ($status === 'canceled') {
    $update['canceled_at'] = date('Y-m-d H:i:s');
} elseif (in_array($status, ['active', 'trialing'], true)) {
    // Reactivating clears any prior cancellation.
    $update['canceled_at'] = null;
    $update['cancel_at_period_end'] = 0;
}

TenantSubscription::update($id, $update);

logAudit('subscription_status_changed', 'tenant_subscription', $id, [
    'tenant_id'  => (int) $subscription['tenant_id'],
    'old_status' => $subscription['status'],
    'new_status' => $status,
]);

flashMessage('success', 'Subscription status updated.');
redirect('/billing/subscriptions');
