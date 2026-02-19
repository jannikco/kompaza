<?php

use App\Models\User;

$tenantId = currentTenantId();
$customers = User::customersByTenant($tenantId, null, 10000, 0);

$filename = 'kunder_' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// BOM for Excel UTF-8 compatibility
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Header row
fputcsv($output, ['Navn', 'E-mail', 'Virksomhed', 'Telefon', 'By', 'Land', 'Oprettet'], ';');

foreach ($customers as $customer) {
    fputcsv($output, [
        $customer['name'] ?? '',
        $customer['email'] ?? '',
        $customer['company'] ?? '',
        $customer['phone'] ?? '',
        $customer['city'] ?? '',
        $customer['country'] ?? '',
        formatDate($customer['created_at'], 'd-m-Y H:i'),
    ], ';');
}

fclose($output);
exit;
