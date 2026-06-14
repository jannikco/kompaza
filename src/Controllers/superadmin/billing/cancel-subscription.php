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

$id = (int) sanitize($_POST['id'] ?? 0);

$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM tenant_subscriptions WHERE id = ?");
$stmt->execute([$id]);
$subscription = $stmt->fetch();

if (!$subscription) {
    flashMessage('error', 'Subscription not found.');
    redirect('/billing/subscriptions');
}

// Cancel immediately: mark canceled and stamp canceled_at.
TenantSubscription::update($id, [
    'status'               => 'canceled',
    'cancel_at_period_end' => 0,
    'canceled_at'          => date('Y-m-d H:i:s'),
]);

logAudit('subscription_canceled', 'tenant_subscription', $id, [
    'tenant_id' => (int) $subscription['tenant_id'],
]);

flashMessage('success', 'Subscription canceled.');
redirect('/billing/subscriptions');
