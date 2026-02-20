<?php

use App\Auth\Auth;
use App\Models\LessonAttachment;

Auth::requireCustomer();

$tenant = currentTenant();
$tenantId = currentTenantId();
$userId = currentUserId();
$attachmentId = (int)($_GET['id'] ?? 0);

$attachment = LessonAttachment::find($attachmentId);
if (!$attachment || $attachment['tenant_id'] != $tenantId) {
    flashMessage('error', 'Attachment not found.');
    redirect('/courses');
}

// Verify user is enrolled in the course this lesson belongs to
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("
    SELECT ce.id FROM course_enrollments ce
    JOIN course_lessons cl ON cl.course_id = ce.course_id
    WHERE cl.id = ? AND ce.user_id = ? AND ce.status = 'active'
");
$stmt->execute([$attachment['lesson_id'], $userId]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    flashMessage('error', 'You must be enrolled in the course to download attachments.');
    redirect('/courses');
}

// Increment download count
LessonAttachment::incrementDownloads($attachmentId);

// Serve the file
$fileInfo = getPrivateFileUrl($attachment['file_path']);
if ($fileInfo) {
    if ($fileInfo['type'] === 's3') {
        header('Location: ' . $fileInfo['url']);
        exit;
    } else {
        $filePath = $fileInfo['path'];
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $attachment['title'] . '.' . $attachment['file_type'] . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}

flashMessage('error', 'File not found.');
redirect('/courses');
