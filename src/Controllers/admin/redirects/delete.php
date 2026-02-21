<?php

use App\Models\Redirect;

if (!isPost()) redirect('/admin/redirects');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Try again.');
    redirect('/admin/redirects');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/redirects');

$redirect = Redirect::find($id, $tenantId);
if (!$redirect) {
    flashMessage('error', 'Redirect not found.');
    redirect('/admin/redirects');
}

Redirect::delete($id, $tenantId);

logAudit('redirect_deleted', 'redirect', $id);
flashMessage('success', 'Redirect deleted.');
redirect('/admin/redirects');
