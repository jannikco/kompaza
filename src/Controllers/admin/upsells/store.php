<?php

use App\Models\UpsellOffer;

if (!isPost()) redirect('/admin/upsells');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/upsells/create');
}

$tenantId = currentTenantId();
$name = sanitize($_POST['name'] ?? '');

if (!$name) {
    flashMessage('error', 'Name is required.');
    redirect('/admin/upsells/create');
}

// Handle image upload
$imagePath = null;
if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        $imagePath = uploadPublicFile($_FILES['image']['tmp_name'], 'upsells', 'upsell', $ext);
    }
}

$triggerIds = !empty($_POST['trigger_product_ids'])
    ? json_encode(array_map('intval', explode(',', $_POST['trigger_product_ids'])))
    : null;

$id = UpsellOffer::create([
    'tenant_id' => $tenantId,
    'name' => $name,
    'headline' => sanitize($_POST['headline'] ?? ''),
    'description' => $_POST['description'] ?? '',
    'product_id' => (int)($_POST['product_id'] ?? 0),
    'offer_price_dkk' => (float)($_POST['offer_price_dkk'] ?? 0),
    'original_price_dkk' => !empty($_POST['original_price_dkk']) ? (float)$_POST['original_price_dkk'] : null,
    'trigger_product_ids' => $triggerIds,
    'offer_type' => sanitize($_POST['offer_type'] ?? 'upsell'),
    'parent_upsell_id' => !empty($_POST['parent_upsell_id']) ? (int)$_POST['parent_upsell_id'] : null,
    'button_text' => sanitize($_POST['button_text'] ?? 'Yes, Add This To My Order!'),
    'decline_text' => sanitize($_POST['decline_text'] ?? 'No thanks, I\'ll pass'),
    'image_path' => $imagePath,
    'sort_order' => (int)($_POST['sort_order'] ?? 0),
    'status' => sanitize($_POST['status'] ?? 'active'),
]);

logAudit('upsell_offer_created', 'upsell_offer', $id);
flashMessage('success', 'Offer created.');
redirect('/admin/upsells');
