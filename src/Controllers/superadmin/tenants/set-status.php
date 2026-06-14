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

$status = sanitize($_POST['status'] ?? '');
$validStatuses = ['active', 'suspended'];
if (!in_array($status, $validStatuses, true)) {
    flashMessage('error', 'Invalid status.');
    redirect('/tenants/show?id=' . $id);
}

Tenant::update($id, ['status' => $status]);

logAudit('tenant_status_changed', 'tenant', $id, ['status' => $status]);
flashMessage('success', 'Tenant ' . ($status === 'suspended' ? 'suspended' : 'activated') . '.');

redirect('/tenants/show?id=' . $id);
