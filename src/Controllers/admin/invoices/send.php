<?php

use App\Auth\Auth;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoiceService;
use App\Services\EmailServiceFactory;

Auth::requireTenantAdmin();

$tenantId = currentTenantId();
$tenant = currentTenant();

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

if (!in_array($invoice['status'], ['draft', 'sent', 'viewed'])) {
    flashMessage('error', 'This invoice cannot be sent.');
    redirect('/admin/invoices/edit?id=' . $id);
}

// Generate view URL
$viewUrl = url('/invoice/view/' . $invoice['view_token']);

// Send email
try {
    $emailService = EmailServiceFactory::create($tenant);
    if (!$emailService->isConfigured()) {
        flashMessage('error', 'Email service not configured.');
        redirect('/admin/invoices/edit?id=' . $id);
    }

    $companyName = $tenant['company_name'] ?? $tenant['name'] ?? 'Company';
    $subject = 'Invoice ' . $invoice['invoice_number'] . ' from ' . $companyName;

    $html = '<h2>Invoice ' . h($invoice['invoice_number']) . '</h2>';
    $html .= '<p>Hi ' . h($invoice['customer_name']) . ',</p>';
    $html .= '<p>Please find your invoice below.</p>';
    $html .= '<table style="width:100%;border-collapse:collapse;margin:20px 0;">';
    $html .= '<tr style="background:#f3f4f6;"><th style="padding:8px 12px;text-align:left;">Amount Due</th><th style="padding:8px 12px;text-align:left;">Due Date</th></tr>';
    $html .= '<tr><td style="padding:8px 12px;font-size:18px;font-weight:bold;">' . formatMoney($invoice['total_dkk'] - $invoice['amount_paid_dkk']) . '</td>';
    $html .= '<td style="padding:8px 12px;">' . date('d/m/Y', strtotime($invoice['due_date'])) . '</td></tr>';
    $html .= '</table>';
    $html .= '<p><a href="' . h($viewUrl) . '" style="display:inline-block;padding:12px 24px;background:#4F46E5;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">View Invoice</a></p>';
    if ($invoice['notes']) {
        $html .= '<p style="color:#666;margin-top:20px;">' . nl2br(h($invoice['notes'])) . '</p>';
    }
    $html .= '<p style="color:#999;font-size:12px;margin-top:30px;">From ' . h($companyName) . '</p>';

    $emailService->sendTransactionalEmail($invoice['customer_email'], $subject, $html);

    Invoice::update($id, ['status' => 'sent'], $tenantId);
    logAudit('invoice_sent', 'invoice', $id, ['email' => $invoice['customer_email']]);

    flashMessage('success', 'Invoice sent to ' . $invoice['customer_email']);
} catch (Exception $e) {
    if (APP_DEBUG) {
        error_log('Invoice send failed: ' . $e->getMessage());
    }
    flashMessage('error', 'Failed to send invoice email.');
}

redirect('/admin/invoices/edit?id=' . $id);
