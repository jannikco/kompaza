<?php

use App\Models\User;

if (!isPost()) redirect('/admin/kunder');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/kunder');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/kunder');

$customer = User::find($id);
if (!$customer || $customer['tenant_id'] != $tenantId || $customer['role'] !== 'customer') {
    flashMessage('error', 'Kunde ikke fundet.');
    redirect('/admin/kunder');
}

User::delete($id);

logAudit('customer_deleted', 'user', $id);
flashMessage('success', 'Kunde slettet.');
redirect('/admin/kunder');
