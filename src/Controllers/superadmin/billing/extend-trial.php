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

$id   = (int) sanitize($_POST['id'] ?? 0);
$days = (int) sanitize($_POST['days'] ?? 0);

if ($days < 1 || $days > 365) {
    flashMessage('error', 'Please choose between 1 and 365 days.');
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

// Extend from the existing trial end (if still in the future) or from now.
$base = $subscription['trial_ends_at'] ?? null;
$baseTs = ($base && strtotime($base) > time()) ? strtotime($base) : time();
$newTrialEnds = date('Y-m-d H:i:s', strtotime("+{$days} days", $baseTs));

$update = [
    'trial_ends_at' => $newTrialEnds,
];

// If the subscription had lapsed, re-open the trial.
if (in_array($subscription['status'], ['canceled', 'past_due', 'unpaid', 'incomplete_expired'], true)) {
    $update['status'] = 'trialing';
    $update['canceled_at'] = null;
}

TenantSubscription::update($id, $update);

logAudit('subscription_trial_extended', 'tenant_subscription', $id, [
    'tenant_id'     => (int) $subscription['tenant_id'],
    'days'          => $days,
    'trial_ends_at' => $newTrialEnds,
]);

flashMessage('success', "Trial extended by {$days} days.");
redirect('/billing/subscriptions');
