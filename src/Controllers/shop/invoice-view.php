<?php

use App\Models\Invoice;
use App\Models\InvoiceItem;

$invoice = Invoice::findByToken($token);
if (!$invoice) {
    http_response_code(404);
    view('errors/404');
    exit;
}

// Mark as viewed
Invoice::markViewed($invoice['id']);

$items = InvoiceItem::allByInvoice($invoice['id']);

view('shop/invoice-view', [
    'invoice' => $invoice,
    'items' => $items,
]);
