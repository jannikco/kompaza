<?php

use App\Models\CourseLesson;
use App\Database\Database;

header('Content-Type: application/json');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$tenantId = currentTenantId();
$lessonId = (int)($_POST['lesson_id'] ?? 0);
$uploadId = $_POST['upload_id'] ?? '';
$originalFilename = $_POST['original_filename'] ?? 'video.mp4';
$fileSize = (int)($_POST['file_size'] ?? 0);
$totalChunks = (int)($_POST['total_chunks'] ?? 0);

if (!$lessonId || !$uploadId) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

$lesson = CourseLesson::find($lessonId, $tenantId);
if (!$lesson) {
    echo json_encode(['success' => false, 'error' => 'Lesson not found']);
    exit;
}

$tempDir = STORAGE_PATH . '/videos/' . $tenantId . '/temp/' . $uploadId;

// Verify all chunks exist
for ($i = 0; $i < $totalChunks; $i++) {
    $chunkPath = $tempDir . '/chunk_' . str_pad($i, 5, '0', STR_PAD_LEFT);
    if (!file_exists($chunkPath)) {
        echo json_encode(['success' => false, 'error' => 'Missing chunk ' . $i]);
        exit;
    }
}

// Reassemble file
$outputDir = STORAGE_PATH . '/videos/' . $tenantId;
if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);

$ext = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION)) ?: 'mp4';
$assembledFilename = 'lesson_' . $lessonId . '_' . time() . '.' . $ext;
$assembledPath = $outputDir . '/' . $assembledFilename;

$outFile = fopen($assembledPath, 'wb');
for ($i = 0; $i < $totalChunks; $i++) {
    $chunkPath = $tempDir . '/chunk_' . str_pad($i, 5, '0', STR_PAD_LEFT);
    $chunk = fopen($chunkPath, 'rb');
    stream_copy_to_stream($chunk, $outFile);
    fclose($chunk);
    unlink($chunkPath);
}
fclose($outFile);

// Clean up temp directory
@rmdir($tempDir);

// Update lesson record
CourseLesson::update($lessonId, [
    'video_original_filename' => $originalFilename,
    'video_file_size_bytes' => $fileSize,
    'video_status' => 'pending',
]);

// Create transcode job
$db = Database::getConnection();
$stmt = $db->prepare("
    INSERT INTO video_transcode_jobs (tenant_id, lesson_id, source_local_path, status, created_at)
    VALUES (?, ?, ?, 'pending', NOW())
");
$stmt->execute([$tenantId, $lessonId, $assembledPath]);

echo json_encode([
    'success' => true,
    'message' => 'Upload complete. Transcoding queued.',
    'lesson_id' => $lessonId,
]);
