<?php

use App\Models\CourseLesson;

header('Content-Type: application/json');

$tenantId = currentTenantId();
$lessonId = (int)($_GET['lesson_id'] ?? 0);

if (!$lessonId) {
    echo json_encode(['error' => 'Missing lesson_id']);
    exit;
}

$lesson = CourseLesson::find($lessonId, $tenantId);
if (!$lesson) {
    echo json_encode(['error' => 'Lesson not found']);
    exit;
}

echo json_encode([
    'status' => $lesson['video_status'] ?? 'none',
    'duration' => $lesson['video_duration_seconds'],
    'filename' => $lesson['video_original_filename'],
    'error' => $lesson['video_error_message'],
]);
