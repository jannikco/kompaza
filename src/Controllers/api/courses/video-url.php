<?php

use App\Models\CourseLesson;
use App\Models\CourseEnrollment;
use App\Services\S3Service;

header('Content-Type: application/json');

$lessonId = (int)($_GET['lesson_id'] ?? 0);
$tenantId = currentTenantId();
$userId = currentUserId();

if (!$lessonId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing lesson_id']);
    exit;
}

$lesson = CourseLesson::find($lessonId, $tenantId);
if (!$lesson) {
    http_response_code(404);
    echo json_encode(['error' => 'Lesson not found']);
    exit;
}

// Check access: either preview lesson or enrolled user
if (!$lesson['is_preview']) {
    if (!$userId) {
        http_response_code(401);
        echo json_encode(['error' => 'Login required']);
        exit;
    }
    $enrollment = CourseEnrollment::findByUserAndCourse($userId, $lesson['course_id']);
    if (!$enrollment) {
        http_response_code(403);
        echo json_encode(['error' => 'Not enrolled']);
        exit;
    }
}

if (!$lesson['video_s3_key'] || $lesson['video_status'] !== 'ready') {
    http_response_code(404);
    echo json_encode(['error' => 'Video not available']);
    exit;
}

if (!S3Service::isConfigured()) {
    http_response_code(500);
    echo json_encode(['error' => 'Video storage not configured']);
    exit;
}

$s3 = new S3Service();
$url = $s3->getPresignedUrl($lesson['video_s3_key'], 14400); // 4 hours

echo json_encode([
    'url' => $url,
    'duration' => $lesson['video_duration_seconds'],
]);
