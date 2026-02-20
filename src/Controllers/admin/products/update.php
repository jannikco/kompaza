<?php

use App\Models\Product;

if (!isPost()) redirect('/admin/produkter');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/produkter');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/produkter');

$product = Product::find($id, $tenantId);
if (!$product) {
    flashMessage('error', 'Produkt ikke fundet.');
    redirect('/admin/produkter');
}

$name = sanitize($_POST['name'] ?? '');
$slug = sanitize($_POST['slug'] ?? '') ?: slugify($name);

if (!$name) {
    flashMessage('error', 'Produktnavn er påkrævet.');
    redirect('/admin/produkter/rediger?id=' . $id);
}

$data = [
    'slug' => $slug,
    'name' => $name,
    'description' => $_POST['description'] ?? null,
    'short_description' => sanitize($_POST['short_description'] ?? ''),
    'price_dkk' => (float)($_POST['price_dkk'] ?? 0),
    'compare_price_dkk' => !empty($_POST['compare_price_dkk']) ? (float)$_POST['compare_price_dkk'] : null,
    'sku' => sanitize($_POST['sku'] ?? ''),
    'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
    'track_stock' => isset($_POST['track_stock']) ? 1 : 0,
    'category' => sanitize($_POST['category'] ?? ''),
    'tags' => !empty($_POST['tags']) ? sanitize($_POST['tags']) : null,
    'is_digital' => isset($_POST['is_digital']) ? 1 : 0,
    'digital_file_path' => sanitize($_POST['digital_file_path'] ?? ''),
    'status' => sanitize($_POST['status'] ?? 'draft'),
    'sort_order' => (int)($_POST['sort_order'] ?? 0),
];

// Handle image replacement
if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        flashMessage('error', 'Kun billeder (jpg, png, webp, gif) er tilladt.');
        redirect('/admin/produkter/rediger?id=' . $id);
    }
    // Delete old image
    if ($product['image_path']) {
        deleteUploadedFile($product['image_path']);
    }
    $data['image_path'] = uploadPublicFile($_FILES['image']['tmp_name'], 'products', 'prod', $ext);
}

Product::update($id, $data);

logAudit('product_updated', 'product', $id);
flashMessage('success', 'Produkt opdateret.');
redirect('/admin/produkter');
