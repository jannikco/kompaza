<?php

use App\Auth\Auth;
use App\Models\Invoice;

Auth::requireTenantAdmin();

$tenantId = currentTenantId();
$status = $_GET['status'] ?? null;

// Mark overdue invoices
Invoice::markOverdue($tenantId);

$invoices = Invoice::allByTenant($tenantId, $status);

// Stats
$totalCount = Invoice::countByTenant($tenantId);
$draftCount = Invoice::countByTenant($tenantId, 'draft');
$sentCount = Invoice::countByTenant($tenantId, 'sent') + Invoice::countByTenant($tenantId, 'viewed');
$overdueCount = Invoice::countByTenant($tenantId, 'overdue');
$paidCount = Invoice::countByTenant($tenantId, 'paid');
$outstanding = Invoice::totalOutstanding($tenantId);

view('admin/invoices/index', [
    'invoices' => $invoices,
    'status' => $status,
    'totalCount' => $totalCount,
    'draftCount' => $draftCount,
    'sentCount' => $sentCount,
    'overdueCount' => $overdueCount,
    'paidCount' => $paidCount,
    'outstanding' => $outstanding,
]);
