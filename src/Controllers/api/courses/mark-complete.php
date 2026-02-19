<?php

use App\Models\CourseLesson;
use App\Models\CourseEnrollment;
use App\Models\CourseProgress;

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$lessonId = (int)($input['lesson_id'] ?? 0);
$userId = currentUserId();
$tenantId = currentTenantId();

if (!$userId) {
    http_response_code(401);
    echo json_encode(['error' => 'Login required']);
    exit;
}

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

$enrollment = CourseEnrollment::findByUserAndCourse($userId, $lesson['course_id']);
if (!$enrollment) {
    http_response_code(403);
    echo json_encode(['error' => 'Not enrolled']);
    exit;
}

CourseProgress::markComplete($tenantId, $enrollment['id'], $lessonId, $userId);
CourseEnrollment::updateLastAccessed($enrollment['id']);

// Reload enrollment for fresh progress data
$enrollment = CourseEnrollment::find($enrollment['id']);

echo json_encode([
    'success' => true,
    'completed_lessons' => (int)$enrollment['completed_lessons'],
    'total_lessons' => (int)$enrollment['total_lessons'],
    'progress_percent' => (float)$enrollment['progress_percent'],
]);
