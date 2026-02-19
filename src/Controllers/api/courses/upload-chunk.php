<?php

header('Content-Type: application/json');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$tenantId = currentTenantId();
$lessonId = (int)($_POST['lesson_id'] ?? 0);
$uploadId = $_POST['upload_id'] ?? '';
$chunkIndex = (int)($_POST['chunk_index'] ?? 0);
$totalChunks = (int)($_POST['total_chunks'] ?? 0);

if (!$lessonId || !$uploadId || !$totalChunks) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

// Verify lesson belongs to tenant
$lesson = \App\Models\CourseLesson::find($lessonId, $tenantId);
if (!$lesson) {
    echo json_encode(['success' => false, 'error' => 'Lesson not found']);
    exit;
}

if (!isset($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No chunk data received']);
    exit;
}

// Save chunk to temp directory
$tempDir = STORAGE_PATH . '/videos/' . $tenantId . '/temp/' . $uploadId;
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0755, true);
}

$chunkPath = $tempDir . '/chunk_' . str_pad($chunkIndex, 5, '0', STR_PAD_LEFT);
move_uploaded_file($_FILES['chunk']['tmp_name'], $chunkPath);

echo json_encode([
    'success' => true,
    'chunk_index' => $chunkIndex,
    'received' => $chunkIndex + 1,
    'total' => $totalChunks,
]);
