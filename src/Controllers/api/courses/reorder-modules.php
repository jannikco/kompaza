<?php

use App\Models\CourseModule;

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$courseId = (int)($input['course_id'] ?? 0);
$orderedIds = $input['module_ids'] ?? [];

if (!$courseId || empty($orderedIds)) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

CourseModule::reorder($courseId, $orderedIds);
echo json_encode(['success' => true]);
