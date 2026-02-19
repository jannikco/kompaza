<?php

use App\Models\EmailSignup;

$tenantId = currentTenantId();
$tenant = currentTenant();

// Fetch all signups (no pagination for export)
$signups = EmailSignup::allByTenant($tenantId, 100000, 0);

$filename = 'signups_' . ($tenant['slug'] ?? 'export') . '_' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// BOM for Excel UTF-8 compatibility
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Header row
fputcsv($output, ['Email', 'Navn', 'Kilde', 'Kilde slug', 'Dato'], ';');

foreach ($signups as $signup) {
    fputcsv($output, [
        $signup['email'],
        $signup['name'] ?? '',
        $signup['source_type'] ?? '',
        $signup['source_slug'] ?? '',
        $signup['created_at'],
    ], ';');
}

fclose($output);
exit;
