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

$name = sanitize($_POST['name'] ?? '');
if (!$name) {
    flashMessage('error', 'Name is required.');
    redirect('/admin/upsells/edit?id=' . $id);
}

$updateData = [
    'name' => $name,
    'headline' => sanitize($_POST['headline'] ?? ''),
    'description' => $_POST['description'] ?? '',
    'product_id' => (int)($_POST['product_id'] ?? 0),
    'offer_price_dkk' => (float)($_POST['offer_price_dkk'] ?? 0),
    'original_price_dkk' => !empty($_POST['original_price_dkk']) ? (float)$_POST['original_price_dkk'] : null,
    'trigger_product_ids' => !empty($_POST['trigger_product_ids']) ? json_encode(array_map('intval', explode(',', $_POST['trigger_product_ids']))) : null,
    'offer_type' => sanitize($_POST['offer_type'] ?? 'upsell'),
    'parent_upsell_id' => !empty($_POST['parent_upsell_id']) ? (int)$_POST['parent_upsell_id'] : null,
    'button_text' => sanitize($_POST['button_text'] ?? 'Yes, Add This To My Order!'),
    'decline_text' => sanitize($_POST['decline_text'] ?? 'No thanks, I\'ll pass'),
    'sort_order' => (int)($_POST['sort_order'] ?? 0),
    'status' => sanitize($_POST['status'] ?? 'active'),
];

// Handle image upload
if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        if ($offer['image_path']) {
            deleteUploadedFile($offer['image_path']);
        }
        $updateData['image_path'] = uploadPublicFile($_FILES['image']['tmp_name'], 'upsells', 'upsell', $ext);
    }
}

UpsellOffer::update($id, $updateData);

logAudit('upsell_offer_updated', 'upsell_offer', $id);
flashMessage('success', 'Offer updated.');
redirect('/admin/upsells');
