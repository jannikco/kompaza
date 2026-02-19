<?php

use App\Models\DownloadToken;
use App\Models\LeadMagnet;

$tenant = currentTenant();
$tenantId = currentTenantId();

// Validate the download token
$tokenRecord = DownloadToken::isValid($token);

if (!$tokenRecord) {
    http_response_code(404);
    view('errors/404');
    exit;
}

// Verify token belongs to this tenant
if ((int)$tokenRecord['tenant_id'] !== $tenantId) {
    http_response_code(404);
    view('errors/404');
    exit;
}

// Load the lead magnet to get the PDF filename
$leadMagnet = LeadMagnet::find($tokenRecord['tokenable_id'], $tenantId);

if (!$leadMagnet || empty($leadMagnet['pdf_filename'])) {
    http_response_code(404);
    view('errors/404');
    exit;
}

// Build the file path
$filePath = STORAGE_PATH . '/pdfs/' . $tenantId . '/' . $leadMagnet['pdf_filename'];

if (!file_exists($filePath)) {
    http_response_code(404);
    view('errors/404');
    exit;
}

// Increment download count
DownloadToken::incrementDownloads($tokenRecord['id']);

// Determine the download filename
$downloadName = $leadMagnet['pdf_original_name'] ?? $leadMagnet['pdf_filename'];

// Serve the PDF file
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($downloadName) . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

readfile($filePath);
exit;
