<?php

use App\Models\UpsellOffer;

if (!isPost()) redirect('/admin/upsells');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/upsells');
}

$id = (int)($_POST['id'] ?? 0);
$tenantId = currentTenantId();

$offer = UpsellOffer::find($id, $tenantId);
if (!$offer) {
    flashMessage('error', 'Offer not found.');
    redirect('/admin/upsells');
}

if ($offer['image_path']) {
    deleteUploadedFile($offer['image_path']);
}

UpsellOffer::delete($id, $tenantId);

logAudit('upsell_offer_deleted', 'upsell_offer', $id);
flashMessage('success', 'Offer deleted.');
redirect('/admin/upsells');
