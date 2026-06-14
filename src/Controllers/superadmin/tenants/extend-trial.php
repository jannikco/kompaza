<?php

use App\Models\Tenant;

if (!isPost()) {
    redirect('/tenants');
}

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request. Please try again.');
    redirect('/tenants');
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) {
    flashMessage('error', 'Tenant not found.');
    redirect('/tenants');
}

$tenant = Tenant::find($id);
if (!$tenant) {
    flashMessage('error', 'Tenant not found.');
    redirect('/tenants');
}

// Extend 14 days. If the trial is still in the future, extend from there; otherwise from now.
$base = (!empty($tenant['trial_ends_at']) && strtotime($tenant['trial_ends_at']) > time())
    ? strtotime($tenant['trial_ends_at'])
    : time();
$newTrialEnd = date('Y-m-d H:i:s', strtotime('+14 days', $base));

Tenant::update($id, ['trial_ends_at' => $newTrialEnd]);

logAudit('tenant_trial_extended', 'tenant', $id, ['trial_ends_at' => $newTrialEnd]);
flashMessage('success', 'Trial extended to ' . date('d M Y', strtotime($newTrialEnd)) . '.');

redirect('/tenants/show?id=' . $id);
