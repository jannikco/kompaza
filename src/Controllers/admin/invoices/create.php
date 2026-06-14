<?php

use App\Auth\Auth;
use App\Models\Invoice;

Auth::requireTenantAdmin();

$tenantId = currentTenantId();
$invoiceNumber = Invoice::generateNumber($tenantId);

// Pre-fill from customer if provided
$customerId = $_GET['customer_id'] ?? null;
$customer = null;
if ($customerId) {
    $customer = \App\Models\User::find($customerId);
}

view('admin/invoices/form', [
    'invoice' => null,
    'invoiceNumber' => $invoiceNumber,
    'customer' => $customer,
    'items' => [],
]);
