<?php

use App\Auth\Auth;
use App\Models\CustomerMembership;
use App\Models\MembershipContentSelection;
use App\Models\CourseEnrollment;
use App\Models\LiveSession;
use App\Models\LiveSessionRegistration;
use App\Services\MembershipGuard;

Auth::requireCustomer();

$tenant = currentTenant();
$tenantId = currentTenantId();
$userId = currentUserId();

$membership = CustomerMembership::findActiveByUser($userId, $tenantId);
if (!$membership) {
    flashMessage('error', 'You do not have an active membership.');
    redirect('/membership');
}

// Get content selections
$courseSelections = MembershipContentSelection::allByUser($userId, $tenantId, 'course');
$ebookSelections = MembershipContentSelection::allByUser($userId, $tenantId, 'ebook');

// Get enrolled courses
$enrollments = CourseEnrollment::allByUser($userId, $tenantId);

// Get upcoming live sessions if membership allows
$upcomingSessions = [];
$registeredSessionIds = [];
if ($membership['can_access_live_qa']) {
    $allUpcoming = LiveSession::upcoming($tenantId);
    $tierLevel = (int)$membership['tier_level'];
    foreach ($allUpcoming as $session) {
        if ((int)$session['min_tier_level'] <= $tierLevel) {
            $upcomingSessions[] = $session;
        }
    }
    // Get registration status
    foreach ($upcomingSessions as $session) {
        $reg = LiveSessionRegistration::find($session['id'], $userId);
        if ($reg) {
            $registeredSessionIds[] = (int)$session['id'];
        }
    }
}

view('shop/membership-dashboard', [
    'tenant' => $tenant,
    'membership' => $membership,
    'courseSelections' => $courseSelections,
    'ebookSelections' => $ebookSelections,
    'enrollments' => $enrollments,
    'upcomingSessions' => $upcomingSessions,
    'registeredSessionIds' => $registeredSessionIds,
]);
