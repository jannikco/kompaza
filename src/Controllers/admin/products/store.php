<?php

use App\Models\Product;

if (!isPost()) redirect('/admin/produkter');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/produkter/opret');
}

$tenantId = currentTenantId();

$name = sanitize($_POST['name'] ?? '');
$slug = sanitize($_POST['slug'] ?? '') ?: slugify($name);

if (!$name) {
    flashMessage('error', 'Produktnavn er påkrævet.');
    redirect('/admin/produkter/opret');
}

// Handle image upload
$imagePath = null;
if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imgOriginal = $_FILES['image']['name'];
    $ext = strtolower(pathinfo($imgOriginal, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        flashMessage('error', 'Kun billeder (jpg, png, webp, gif) er tilladt.');
        redirect('/admin/produkter/opret');
    }
    $imgFilename = generateUniqueId('prod_') . '.' . $ext;
    $uploadPath = tenantUploadPath('products');
    move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath . '/' . $imgFilename);
    $imagePath = '/uploads/' . $tenantId . '/products/' . $imgFilename;
}

$id = Product::create([
    'tenant_id' => $tenantId,
    'slug' => $slug,
    'name' => $name,
    'description' => $_POST['description'] ?? null,
    'short_description' => sanitize($_POST['short_description'] ?? ''),
    'image_path' => $imagePath,
    'gallery' => $_POST['gallery'] ?? null,
    'price_dkk' => (float)($_POST['price_dkk'] ?? 0),
    'compare_price_dkk' => !empty($_POST['compare_price_dkk']) ? (float)$_POST['compare_price_dkk'] : null,
    'sku' => sanitize($_POST['sku'] ?? ''),
    'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
    'track_stock' => isset($_POST['track_stock']) ? 1 : 0,
    'category' => sanitize($_POST['category'] ?? ''),
    'tags' => sanitize($_POST['tags'] ?? ''),
    'is_digital' => isset($_POST['is_digital']) ? 1 : 0,
    'digital_file_path' => sanitize($_POST['digital_file_path'] ?? ''),
    'status' => sanitize($_POST['status'] ?? 'draft'),
    'sort_order' => (int)($_POST['sort_order'] ?? 0),
]);

logAudit('product_created', 'product', $id);
flashMessage('success', 'Produkt oprettet.');
redirect('/admin/produkter');
