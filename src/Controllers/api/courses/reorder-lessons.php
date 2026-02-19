<?php

use App\Models\CourseLesson;

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$moduleId = (int)($input['module_id'] ?? 0);
$orderedIds = $input['lesson_ids'] ?? [];

if (!$moduleId || empty($orderedIds)) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

CourseLesson::reorder($moduleId, $orderedIds);
echo json_encode(['success' => true]);
