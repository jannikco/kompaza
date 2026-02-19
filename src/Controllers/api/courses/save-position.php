<?php

use App\Models\CourseLesson;
use App\Models\CourseEnrollment;
use App\Models\CourseProgress;

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$lessonId = (int)($input['lesson_id'] ?? 0);
$position = (int)($input['position'] ?? 0);
$watchedPercent = (float)($input['watched_percent'] ?? 0);
$userId = currentUserId();
$tenantId = currentTenantId();

if (!$userId || !$lessonId) {
    echo json_encode(['success' => false]);
    exit;
}

$lesson = CourseLesson::find($lessonId, $tenantId);
if (!$lesson) {
    echo json_encode(['success' => false]);
    exit;
}

$enrollment = CourseEnrollment::findByUserAndCourse($userId, $lesson['course_id']);
if (!$enrollment) {
    echo json_encode(['success' => false]);
    exit;
}

CourseProgress::savePosition($tenantId, $enrollment['id'], $lessonId, $userId, $position, $watchedPercent);
CourseEnrollment::updateLastAccessed($enrollment['id']);

echo json_encode(['success' => true]);
