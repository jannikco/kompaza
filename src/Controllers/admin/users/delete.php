<?php

use App\Models\User;

if (!isPost()) redirect('/admin/users');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/users');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/users');

$user = User::find($id);
if (!$user || $user['tenant_id'] != $tenantId || $user['role'] !== 'tenant_admin') {
    flashMessage('error', 'Bruger ikke fundet.');
    redirect('/admin/users');
}

// Prevent deleting yourself
if ($id == currentUserId()) {
    flashMessage('error', 'Du kan ikke slette din egen konto.');
    redirect('/admin/users');
}

User::delete($id);

logAudit('user_deleted', 'user', $id);
flashMessage('success', 'Bruger slettet.');
redirect('/admin/users');
