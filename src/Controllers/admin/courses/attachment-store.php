<?php

use App\Models\LessonAttachment;

$tenantId = currentTenantId();

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/admin/kurser');
}

$lessonId = (int)($_POST['lesson_id'] ?? 0);
$courseId = (int)($_POST['course_id'] ?? 0);

// Verify lesson belongs to tenant
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("SELECT * FROM course_lessons WHERE id = ? AND tenant_id = ?");
$stmt->execute([$lessonId, $tenantId]);
$lesson = $stmt->fetch();

if (!$lesson) {
    flashMessage('error', 'Lesson not found.');
    redirect('/admin/kurser');
}

if (empty($_FILES['attachment']) || $_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
    flashMessage('error', 'Please select a file to upload.');
    redirect('/admin/kurser/lektion?id=' . $lessonId . '&course_id=' . $courseId);
}

$file = $_FILES['attachment'];
$title = trim($_POST['attachment_title'] ?? '');
if (empty($title)) {
    $title = pathinfo($file['name'], PATHINFO_FILENAME);
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'txt', 'csv', 'png', 'jpg', 'jpeg'];

if (!in_array($ext, $allowedExtensions)) {
    flashMessage('error', 'File type not allowed.');
    redirect('/admin/kurser/lektion?id=' . $lessonId . '&course_id=' . $courseId);
}

// Max 50MB
if ($file['size'] > 50 * 1024 * 1024) {
    flashMessage('error', 'File is too large. Maximum 50MB.');
    redirect('/admin/kurser/lektion?id=' . $lessonId . '&course_id=' . $courseId);
}

$filePath = uploadPrivateFile($file['tmp_name'], 'attachments', 'att', $ext);

LessonAttachment::create([
    'tenant_id' => $tenantId,
    'lesson_id' => $lessonId,
    'title' => sanitize($title),
    'file_path' => $filePath,
    'file_type' => $ext,
    'file_size' => $file['size'],
]);

flashMessage('success', 'Attachment uploaded.');
redirect('/admin/kurser/lektion?id=' . $lessonId . '&course_id=' . $courseId);
