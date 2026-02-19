<?php

use App\Models\Ebook;

if (!isPost()) redirect('/admin/eboger');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/eboger');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/eboger');

$ebook = Ebook::find($id, $tenantId);
if (!$ebook) {
    flashMessage('error', 'E-bog ikke fundet.');
    redirect('/admin/eboger');
}

// Delete associated files
if ($ebook['pdf_filename']) {
    $pdfPath = tenantStoragePath() . '/' . $ebook['pdf_filename'];
    if (file_exists($pdfPath)) unlink($pdfPath);
}
if ($ebook['cover_image_path']) {
    $imgPath = PUBLIC_PATH . $ebook['cover_image_path'];
    if (file_exists($imgPath)) unlink($imgPath);
}

Ebook::delete($id, $tenantId);

logAudit('ebook_deleted', 'ebook', $id);
flashMessage('success', 'E-bog slettet.');
redirect('/admin/eboger');
