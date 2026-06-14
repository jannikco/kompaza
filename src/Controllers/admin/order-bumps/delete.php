<?php

use App\Models\OrderBump;

if (!isPost()) redirect('/admin/order-bumps');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/order-bumps');
}

$id = (int)($_POST['id'] ?? 0);
$tenantId = currentTenantId();

$bump = OrderBump::find($id, $tenantId);
if (!$bump) {
    flashMessage('error', 'Order bump not found.');
    redirect('/admin/order-bumps');
}

OrderBump::delete($id, $tenantId);

logAudit('order_bump_deleted', 'order_bump', $id);
flashMessage('success', 'Order bump deleted.');
redirect('/admin/order-bumps');
