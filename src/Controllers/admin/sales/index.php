<?php

require_once CONTROLLERS_PATH . '/../Helpers/admin-layout.php';

use App\Auth\Auth;
use App\Database\Database;
use App\Models\Order;
use App\Models\EbookPurchase;
use App\Models\CourseEnrollment;
use App\Models\QuizAttempt;
use App\Models\Certificate;
use App\Models\ConsultationBooking;
use App\Models\EmailSignup;
use App\Models\User;
use App\Models\Course;
use App\Models\Quiz;

$admin = Auth::admin();
$tenantId = $admin['tenant_id'] ?? null;

// --- Order stats ---
$totalOrders = $tenantId ? Order::countByTenant($tenantId) : 0;
$totalOrderRevenue = $tenantId ? Order::totalRevenueByTenant($tenantId) : 0;
$recentOrders = $tenantId ? Order::recentByTenant($tenantId, 20) : [];

// --- Ebook purchase stats ---
$ebookPurchases = $tenantId ? EbookPurchase::allByTenantId($tenantId) : [];
$ebookSalesCount = $tenantId ? EbookPurchase::countByTenantId($tenantId) : 0;
$ebookRevenue = $tenantId ? EbookPurchase::revenueByTenantId($tenantId) : 0;

// Combined revenue (orders in DKK, ebook in cents)
$totalRevenue = $totalOrderRevenue + ($ebookRevenue / 100);

// --- Customer count ---
$customerCount = $tenantId ? User::countByTenant($tenantId, 'customer') : 0;

// --- Email signup count ---
$emailSignupCount = $tenantId ? EmailSignup::countByTenant($tenantId) : 0;

// --- Course stats (feature-gated) ---
$courseStats = [];
if (tenantFeature('courses') && $tenantId) {
    $courses = Course::allByTenant($tenantId);
    foreach ($courses as $course) {
        $enrollmentCount = CourseEnrollment::countByCourse($course['id']);
        $quizzes = Quiz::getByCourseId($course['id'], $tenantId);
        $quizAttempts = 0;
        $quizPassRate = 0;
        if (!empty($quizzes)) {
            foreach ($quizzes as $quiz) {
                $quizAttempts += QuizAttempt::countByQuiz($quiz['id']);
            }
            // Average pass rate across quizzes
            $passRates = [];
            foreach ($quizzes as $quiz) {
                $rate = QuizAttempt::passRateByQuiz($quiz['id']);
                if ($rate !== null) {
                    $passRates[] = $rate;
                }
            }
            $quizPassRate = !empty($passRates) ? round(array_sum($passRates) / count($passRates), 1) : 0;
        }

        // Count certificates for this course
        $courseCerts = Certificate::getByCourse($course['id'], $tenantId);
        $certCount = count($courseCerts);

        // Completion rate: enrollments with completed_at / total enrollments
        $enrollments = CourseEnrollment::allByCourse($course['id']);
        $completedCount = 0;
        foreach ($enrollments as $enrollment) {
            if (!empty($enrollment['completed_at'])) {
                $completedCount++;
            }
        }
        $completionRate = $enrollmentCount > 0 ? round(($completedCount / $enrollmentCount) * 100, 1) : 0;

        $courseStats[] = [
            'title' => $course['title'],
            'enrolled' => $enrollmentCount,
            'completion_rate' => $completionRate,
            'quiz_attempts' => $quizAttempts,
            'quiz_pass_rate' => $quizPassRate,
            'certificates' => $certCount,
        ];
    }
}

// --- Certificate stats (feature-gated) ---
$certificateCount = 0;
$recentCertificates = [];
if (tenantFeature('courses') && $tenantId) {
    $certificateCount = Certificate::countByTenant($tenantId);
    $allCertificates = Certificate::allByTenant($tenantId);
    $recentCertificates = array_slice($allCertificates, 0, 20);
}

// --- Consultation stats (feature-gated) ---
$consultationCount = 0;
$consultationPending = 0;
$consultationConfirmed = 0;
$consultationCompleted = 0;
if (tenantFeature('consultations') && $tenantId) {
    $consultationCount = ConsultationBooking::countByTenant($tenantId);
    $consultationPending = ConsultationBooking::countByTenant($tenantId, 'pending');
    $consultationConfirmed = ConsultationBooking::countByTenant($tenantId, 'confirmed');
    $consultationCompleted = ConsultationBooking::countByTenant($tenantId, 'completed');
}

// --- Revenue by day (last 30 days) ---
$dailyRevenue = [];
if ($tenantId) {
    $db = Database::getConnection();
    $stmt = $db->prepare("
        SELECT DATE(created_at) as day, COALESCE(SUM(total_dkk), 0) as revenue
        FROM orders
        WHERE tenant_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
          AND status NOT IN ('cancelled', 'refunded')
        GROUP BY DATE(created_at)
        ORDER BY day ASC
    ");
    $stmt->execute([$tenantId]);
    $rows = $stmt->fetchAll();

    // Build a map for all 30 days (fill gaps with 0)
    $revenueMap = [];
    foreach ($rows as $row) {
        $revenueMap[$row['day']] = (float)$row['revenue'];
    }
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $dailyRevenue[] = [
            'date' => $date,
            'label' => date('d M', strtotime($date)),
            'revenue' => $revenueMap[$date] ?? 0,
        ];
    }
}

renderAdminPage('Reports & Sales', 'sales', 'admin/sales/index', compact(
    'totalOrders', 'totalOrderRevenue', 'totalRevenue', 'recentOrders',
    'ebookPurchases', 'ebookSalesCount', 'ebookRevenue',
    'customerCount', 'emailSignupCount',
    'courseStats', 'certificateCount', 'recentCertificates',
    'consultationCount', 'consultationPending', 'consultationConfirmed', 'consultationCompleted',
    'dailyRevenue'
));
