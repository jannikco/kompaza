<?php

use App\Database\Database;

if (!isPost()) redirect('/admin/signups');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/signups');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/signups');

// Verify the signup belongs to this tenant before deleting
$db = Database::getConnection();
$stmt = $db->prepare("DELETE FROM email_signups WHERE id = ? AND tenant_id = ?");
$stmt->execute([$id, $tenantId]);

if ($stmt->rowCount() === 0) {
    flashMessage('error', 'Tilmelding ikke fundet.');
    redirect('/admin/signups');
}

logAudit('signup_deleted', 'email_signup', $id);
flashMessage('success', 'Tilmelding slettet.');
redirect('/admin/signups');
