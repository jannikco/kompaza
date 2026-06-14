<?php

use App\Auth\Auth;
use App\Models\Invoice;
use App\Models\InvoiceItem;

Auth::requireTenantAdmin();

$tenantId = currentTenantId();

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/admin/invoices');
}

$id = (int)($_POST['id'] ?? 0);
$invoice = Invoice::find($id, $tenantId);
if (!$invoice) {
    flashMessage('error', 'Invoice not found.');
    redirect('/admin/invoices');
}

$taxRate = (float)($_POST['tax_rate'] ?? 25);

// Parse line items
$descriptions = $_POST['item_description'] ?? [];
$quantities = $_POST['item_quantity'] ?? [];
$unitPrices = $_POST['item_unit_price'] ?? [];

$subtotal = 0;
$lineItems = [];
foreach ($descriptions as $i => $desc) {
    $desc = trim($desc);
    if (empty($desc)) continue;
    $qty = (float)($quantities[$i] ?? 1);
    $price = (float)($unitPrices[$i] ?? 0);
    $lineTotal = round($qty * $price, 2);
    $lineItems[] = [
        'description' => $desc,
        'quantity' => $qty,
        'unit_price_dkk' => $price,
        'total_dkk' => $lineTotal,
        'sort_order' => $i,
    ];
    $subtotal += $lineTotal;
}

$discount = (float)($_POST['discount_dkk'] ?? 0);
$discountedSubtotal = max(0, $subtotal - $discount);
$tax = round($discountedSubtotal * ($taxRate / 100), 2);
$total = round($discountedSubtotal + $tax, 2);

$billingAddress = json_encode([
    'street' => sanitize($_POST['billing_street'] ?? ''),
    'city' => sanitize($_POST['billing_city'] ?? ''),
    'postal' => sanitize($_POST['billing_postal'] ?? ''),
    'country' => sanitize($_POST['billing_country'] ?? 'DK'),
]);

Invoice::update($id, [
    'customer_name' => sanitize($_POST['customer_name'] ?? ''),
    'customer_email' => trim($_POST['customer_email'] ?? ''),
    'customer_phone' => sanitize($_POST['customer_phone'] ?? ''),
    'customer_company' => sanitize($_POST['customer_company'] ?? ''),
    'customer_cvr' => sanitize($_POST['customer_cvr'] ?? ''),
    'billing_address' => $billingAddress,
    'subtotal_dkk' => $subtotal,
    'tax_dkk' => $tax,
    'discount_dkk' => $discount,
    'total_dkk' => $total,
    'tax_rate' => $taxRate,
    'issue_date' => $_POST['issue_date'] ?? $invoice['issue_date'],
    'due_date' => $_POST['due_date'] ?? $invoice['due_date'],
    'notes' => sanitize($_POST['notes'] ?? ''),
    'internal_notes' => sanitize($_POST['internal_notes'] ?? ''),
    'payment_terms' => sanitize($_POST['payment_terms'] ?? ''),
    'footer_text' => sanitize($_POST['footer_text'] ?? ''),
]);

// Recreate line items
InvoiceItem::deleteByInvoice($id);
foreach ($lineItems as $item) {
    $item['invoice_id'] = $id;
    InvoiceItem::create($item);
}

flashMessage('success', 'Invoice updated.');
redirect('/admin/invoices/edit?id=' . $id);
