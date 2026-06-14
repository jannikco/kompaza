<?php

use App\Auth\Auth;
use App\Models\CustomerMembership;
use App\Models\MembershipContentSelection;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use App\Models\Course;

Auth::requireCustomer();

if (!isPost()) redirect('/membership/content-selection');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request. Please try again.');
    redirect('/membership/content-selection');
}

$tenantId = currentTenantId();
$userId = currentUserId();

$membership = CustomerMembership::findActiveByUser($userId, $tenantId);
if (!$membership) {
    flashMessage('error', 'You need an active membership to select content.');
    redirect('/membership');
}

$maxCourses = $membership['max_courses'];
$maxEbooks = $membership['max_ebooks'];

$selectedCourseIds = array_map('intval', $_POST['courses'] ?? []);
$selectedEbookIds = array_map('intval', $_POST['ebooks'] ?? []);

// Validate counts against plan limits
if ($maxCourses !== null && count($selectedCourseIds) > (int)$maxCourses) {
    flashMessage('error', 'You can select a maximum of ' . $maxCourses . ' courses.');
    redirect('/membership/content-selection');
}

if ($maxEbooks !== null && count($selectedEbookIds) > (int)$maxEbooks) {
    flashMessage('error', 'You can select a maximum of ' . $maxEbooks . ' ebooks.');
    redirect('/membership/content-selection');
}

// Delete old selections
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("DELETE FROM membership_content_selections WHERE user_id = ? AND tenant_id = ?");
$stmt->execute([$userId, $tenantId]);

// Insert new course selections and auto-enroll
foreach ($selectedCourseIds as $courseId) {
    MembershipContentSelection::create([
        'tenant_id' => $tenantId,
        'user_id' => $userId,
        'content_type' => 'course',
        'content_id' => $courseId,
    ]);

    // Auto-enroll in course if not already enrolled
    $existing = CourseEnrollment::findAnyByUserAndCourse($userId, $courseId);
    if (!$existing) {
        $course = Course::find($courseId, $tenantId);
        if ($course) {
            $totalLessons = CourseLesson::countByCourse($courseId);
            CourseEnrollment::create([
                'tenant_id' => $tenantId,
                'course_id' => $courseId,
                'user_id' => $userId,
                'enrollment_source' => 'membership',
                'total_lessons' => $totalLessons,
            ]);
            Course::incrementEnrollment($courseId);
        }
    } elseif ($existing['status'] !== 'active') {
        CourseEnrollment::update($existing['id'], ['status' => 'active']);
    }
}

// Insert new ebook selections
foreach ($selectedEbookIds as $ebookId) {
    MembershipContentSelection::create([
        'tenant_id' => $tenantId,
        'user_id' => $userId,
        'content_type' => 'ebook',
        'content_id' => $ebookId,
    ]);
}

flashMessage('success', 'Your content selections have been saved!');
redirect('/membership/dashboard');
