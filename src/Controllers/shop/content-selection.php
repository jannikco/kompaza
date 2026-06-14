<?php

use App\Auth\Auth;
use App\Models\CustomerMembership;
use App\Models\MembershipContentSelection;
use App\Models\Course;
use App\Models\Ebook;

Auth::requireCustomer();

$tenant = currentTenant();
$tenantId = currentTenantId();
$userId = currentUserId();

$membership = CustomerMembership::findActiveByUser($userId, $tenantId);
if (!$membership) {
    flashMessage('error', 'You need an active membership to select content.');
    redirect('/membership');
}

// Only show selection UI if the plan has limited picks
$maxCourses = $membership['max_courses'];
$maxEbooks = $membership['max_ebooks'];

if ($maxCourses === null && $maxEbooks === null) {
    // Unlimited plan - no need for selection UI
    flashMessage('info', 'Your plan includes unlimited access to all content.');
    redirect('/membership/dashboard');
}

$tierLevel = (int)$membership['tier_level'];

// Get all published courses/ebooks accessible at this tier level
$db = \App\Database\Database::getConnection();

$stmt = $db->prepare("SELECT * FROM courses WHERE tenant_id = ? AND status = 'published' AND membership_tier_level IS NOT NULL AND membership_tier_level <= ? ORDER BY title");
$stmt->execute([$tenantId, $tierLevel]);
$availableCourses = $stmt->fetchAll();

$stmt = $db->prepare("SELECT * FROM ebooks WHERE tenant_id = ? AND status = 'published' AND membership_tier_level IS NOT NULL AND membership_tier_level <= ? ORDER BY title");
$stmt->execute([$tenantId, $tierLevel]);
$availableEbooks = $stmt->fetchAll();

// Get current selections
$selectedCourseIds = MembershipContentSelection::getSelectedIds($userId, $tenantId, 'course');
$selectedEbookIds = MembershipContentSelection::getSelectedIds($userId, $tenantId, 'ebook');

view('shop/content-selection', [
    'tenant' => $tenant,
    'membership' => $membership,
    'maxCourses' => $maxCourses,
    'maxEbooks' => $maxEbooks,
    'availableCourses' => $availableCourses,
    'availableEbooks' => $availableEbooks,
    'selectedCourseIds' => $selectedCourseIds,
    'selectedEbookIds' => $selectedEbookIds,
]);
