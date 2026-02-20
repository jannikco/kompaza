<?php

use App\Models\LessonAttachment;

$tenantId = currentTenantId();

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/admin/kurser');
}

$attachmentId = (int)($_POST['id'] ?? 0);
$lessonId = (int)($_POST['lesson_id'] ?? 0);
$courseId = (int)($_POST['course_id'] ?? 0);

$attachment = LessonAttachment::find($attachmentId);
if (!$attachment || $attachment['tenant_id'] != $tenantId) {
    flashMessage('error', 'Attachment not found.');
    redirect('/admin/kurser');
}

// Delete the file
deleteUploadedFile($attachment['file_path']);

LessonAttachment::delete($attachmentId, $tenantId);

flashMessage('success', 'Attachment deleted.');
redirect('/admin/kurser/lektion?id=' . $lessonId . '&course_id=' . $courseId);
