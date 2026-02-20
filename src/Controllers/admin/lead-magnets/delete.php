<?php

use App\Models\LeadMagnet;

if (!isPost()) redirect('/admin/lead-magnets');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/lead-magnets');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/lead-magnets');

$leadMagnet = LeadMagnet::find($id, $tenantId);
if (!$leadMagnet) {
    flashMessage('error', 'Lead magnet ikke fundet.');
    redirect('/admin/lead-magnets');
}

// Delete associated files
if ($leadMagnet['pdf_filename']) {
    deleteUploadedFile($leadMagnet['pdf_filename']);
}
if ($leadMagnet['hero_image_path']) {
    deleteUploadedFile($leadMagnet['hero_image_path']);
}

LeadMagnet::delete($id, $tenantId);

logAudit('lead_magnet_deleted', 'lead_magnet', $id);
flashMessage('success', 'Lead magnet slettet.');
redirect('/admin/lead-magnets');
