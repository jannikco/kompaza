<?php

use App\Auth\Auth;
use App\Models\Invoice;
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

if (in_array($invoice['status'], ['draft', 'paid', 'cancelled'])) {
    flashMessage('error', 'Cannot send reminder for this invoice.');
    redirect('/admin/invoices/edit?id=' . $id);
}

try {
    $emailService = EmailServiceFactory::create($tenantId);
    if (!$emailService->isConfigured()) {
        flashMessage('error', 'Email service not configured.');
        redirect('/admin/invoices/edit?id=' . $id);
    }

    $companyName = $tenant['company_name'] ?? $tenant['name'] ?? 'Company';
    $viewUrl = url('/invoice/view/' . $invoice['view_token']);
    $amountDue = (float)$invoice['total_dkk'] - (float)$invoice['amount_paid_dkk'];
    $isOverdue = strtotime($invoice['due_date']) < time();

    $subject = ($isOverdue ? 'Overdue: ' : 'Reminder: ') . 'Invoice ' . $invoice['invoice_number'];

    $html = '<h2>Payment Reminder</h2>';
    $html .= '<p>Hi ' . h($invoice['customer_name']) . ',</p>';
    if ($isOverdue) {
        $html .= '<p>Your invoice <strong>' . h($invoice['invoice_number']) . '</strong> was due on ' . date('d/m/Y', strtotime($invoice['due_date'])) . ' and remains unpaid.</p>';
    } else {
        $html .= '<p>This is a friendly reminder that invoice <strong>' . h($invoice['invoice_number']) . '</strong> is due on ' . date('d/m/Y', strtotime($invoice['due_date'])) . '.</p>';
    }
    $html .= '<p><strong>Amount due: ' . formatMoney($amountDue) . '</strong></p>';
    $html .= '<p><a href="' . h($viewUrl) . '" style="display:inline-block;padding:12px 24px;background:#4F46E5;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;">View Invoice</a></p>';
    $html .= '<p style="color:#999;font-size:12px;margin-top:30px;">From ' . h($companyName) . '</p>';

    $emailService->sendTransactionalEmail($invoice['customer_email'], $subject, $html);

    Invoice::update($id, [
        'reminder_sent_count' => $invoice['reminder_sent_count'] + 1,
        'last_reminder_at' => date('Y-m-d H:i:s'),
    ]);

    logAudit('invoice_reminder_sent', 'invoice', $id, ['email' => $invoice['customer_email']]);
    flashMessage('success', 'Reminder sent to ' . $invoice['customer_email']);
} catch (Exception $e) {
    if (APP_DEBUG) {
        error_log('Invoice reminder failed: ' . $e->getMessage());
    }
    flashMessage('error', 'Failed to send reminder.');
}

redirect('/admin/invoices/edit?id=' . $id);
