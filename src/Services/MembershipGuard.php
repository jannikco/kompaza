<?php

namespace App\Services;

use App\Models\CustomerMembership;
use App\Models\MembershipContentSelection;
use App\Models\MembershipPlan;

class MembershipGuard {
    public static function getMembership($userId, $tenantId): ?array {
        $membership = CustomerMembership::findActiveByUser($userId, $tenantId);
        return $membership ?: null;
    }

    public static function getTierLevel($userId, $tenantId): int {
        return CustomerMembership::getTierLevel($userId, $tenantId);
    }

    public static function canAccess($userId, $tenantId, $requiredTierLevel): bool {
        if ($requiredTierLevel <= 0) return true;
        return self::getTierLevel($userId, $tenantId) >= $requiredTierLevel;
    }

    public static function canAccessCourse($userId, $tenantId, $courseId): bool {
        $db = \App\Database\Database::getConnection();
        $stmt = $db->prepare("SELECT membership_tier_level FROM courses WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$courseId, $tenantId]);
        $course = $stmt->fetch();

        if (!$course || $course['membership_tier_level'] === null) return false;

        $tierLevel = self::getTierLevel($userId, $tenantId);
        $requiredTier = (int)$course['membership_tier_level'];

        if ($tierLevel <= 0 || $tierLevel < $requiredTier) return false;

        // Unlimited access tiers (e.g. premium) - check if max_courses is NULL
        $membership = self::getMembership($userId, $tenantId);
        if (!$membership) return false;

        if ($membership['max_courses'] === null) return true;

        // Limited tier (e.g. Pro) - must have selected this course
        return MembershipContentSelection::exists($userId, $tenantId, 'course', $courseId);
    }

    public static function canAccessEbook($userId, $tenantId, $ebookId): bool {
        $db = \App\Database\Database::getConnection();
        $stmt = $db->prepare("SELECT membership_tier_level FROM ebooks WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$ebookId, $tenantId]);
        $ebook = $stmt->fetch();

        if (!$ebook || $ebook['membership_tier_level'] === null) return false;

        $tierLevel = self::getTierLevel($userId, $tenantId);
        $requiredTier = (int)$ebook['membership_tier_level'];

        if ($tierLevel <= 0 || $tierLevel < $requiredTier) return false;

        $membership = self::getMembership($userId, $tenantId);
        if (!$membership) return false;

        if ($membership['max_ebooks'] === null) return true;

        return MembershipContentSelection::exists($userId, $tenantId, 'ebook', $ebookId);
    }

    public static function requireTier($userId, $tenantId, $minTierLevel): void {
        if (!self::canAccess($userId, $tenantId, $minTierLevel)) {
            flashMessage('error', 'You need to upgrade your membership to access this content.');
            redirect('/membership');
        }
    }
}
