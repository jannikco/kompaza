<?php

use App\Database\Database;

// Platform-wide list of all subscription invoices (cross-tenant).
$db = Database::getConnection();

$stmt = $db->query("
    SELECT si.id, si.tenant_id, si.stripe_invoice_id, si.amount_cents, si.currency,
           si.status, si.invoice_url, si.invoice_pdf, si.period_start, si.period_end,
           si.paid_at, si.created_at,
           t.name AS tenant_name, t.slug AS tenant_slug
    FROM subscription_invoices si
    LEFT JOIN tenants t ON si.tenant_id = t.id
    ORDER BY si.created_at DESC
");
$invoices = $stmt->fetchAll();

// Summary totals across all tenants.
$totals = $db->query("
    SELECT
        COUNT(*) AS total_count,
        COALESCE(SUM(CASE WHEN status = 'paid' THEN amount_cents ELSE 0 END), 0) AS paid_cents,
        COALESCE(SUM(CASE WHEN status IN ('open', 'draft', 'uncollectible') THEN amount_cents ELSE 0 END), 0) AS outstanding_cents
    FROM subscription_invoices
")->fetch();

view('superadmin/billing/invoices', [
    'invoices' => $invoices,
    'totals'   => $totals,
]);
