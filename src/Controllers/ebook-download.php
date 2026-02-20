<?php

use App\Database\Database;
use App\Models\Ebook;

if (!$token) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$db = Database::getConnection();

// Find valid token
$stmt = $db->prepare("
    SELECT * FROM download_tokens
    WHERE token = ?
    AND expires_at > NOW()
    AND downloads < max_downloads
");
$stmt->execute([$token]);
$downloadToken = $stmt->fetch();

if (!$downloadToken) {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="da"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Link udløbet</title><script src="https://cdn.tailwindcss.com"></script></head>';
    echo '<body class="bg-gray-900 min-h-screen flex items-center justify-center"><div class="text-center px-6">';
    echo '<h1 class="text-2xl font-bold text-white mb-4">Download-link udløbet</h1>';
    echo '<p class="text-gray-400 mb-8">Dette download-link er enten udløbet eller er brugt for mange gange.</p>';
    echo '<a href="/" class="text-blue-400 hover:text-blue-300">&larr; Gå til forsiden</a>';
    echo '</div></body></html>';
    exit;
}

// Get ebook file
$ebook = Ebook::find($downloadToken['source_id']);
if (!$ebook || !$ebook['pdf_filename']) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$filePath = STORAGE_PATH . '/books/' . $ebook['pdf_filename'];
$originalName = $ebook['pdf_original_name'] ?? $ebook['pdf_filename'];

if (!file_exists($filePath)) {
    http_response_code(404);
    view('errors/404');
    exit;
}

// Increment download counter
$stmt = $db->prepare("UPDATE download_tokens SET downloads = downloads + 1 WHERE id = ?");
$stmt->execute([$downloadToken['id']]);

// Serve file
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($originalName) . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: private, max-age=0, must-revalidate');

readfile($filePath);
exit;
