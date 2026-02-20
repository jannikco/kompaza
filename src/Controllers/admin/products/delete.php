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

// Delete associated image
if ($product['image_path']) {
    deleteUploadedFile($product['image_path']);
}

Product::delete($id, $tenantId);

logAudit('product_deleted', 'product', $id);
flashMessage('success', 'Produkt slettet.');
redirect('/admin/produkter');
