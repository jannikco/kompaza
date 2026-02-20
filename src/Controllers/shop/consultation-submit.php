<?php

use App\Models\ConsultationBooking;
use App\Services\EmailServiceFactory;

$tenant = currentTenant();
$tenantId = currentTenantId();

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request. Please try again.');
    redirect('/consultation');
}

if (!checkRateLimit(getClientIp(), 'consultation_booking', 5, 3600)) {
    flashMessage('error', 'Too many booking requests. Please try again later.');
    redirect('/consultation');
}

$name = trim($_POST['customer_name'] ?? '');
$email = trim($_POST['customer_email'] ?? '');
$phone = trim($_POST['customer_phone'] ?? '');
$company = trim($_POST['company'] ?? '');
$typeId = $_POST['type_id'] ?? null;
$projectDescription = trim($_POST['project_description'] ?? '');
$preferredDate = trim($_POST['preferred_date'] ?? '');
$preferredTime = trim($_POST['preferred_time'] ?? '');
$urgency = trim($_POST['urgency'] ?? 'medium');

$errors = [];
if (empty($name)) $errors[] = 'Name is required.';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
if (empty($typeId)) $errors[] = 'Please select a consultation type.';
if (empty($preferredDate)) $errors[] = 'Preferred date is required.';
if (!in_array($urgency, ['low', 'medium', 'high'])) $urgency = 'medium';
if (!in_array($preferredTime, ['morning', 'afternoon', 'evening'])) $preferredTime = 'morning';

// Validate preferred date is in the future
if ($preferredDate && strtotime($preferredDate) < strtotime('today')) {
    $errors[] = 'Preferred date must be today or in the future.';
}

// Validate type belongs to this tenant
if ($typeId) {
    $type = ConsultationBooking::findType($typeId, $tenantId);
    if (!$type || $type['status'] !== 'active') {
        $errors[] = 'Invalid consultation type selected.';
    }
}

if (!empty($errors)) {
    $types = ConsultationBooking::getActiveTypes($tenantId);
    view('shop/consultation', [
        'tenant' => $tenant,
        'types' => $types,
        'errors' => $errors,
        'old' => [
            'customer_name' => $name,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'company' => $company,
            'type_id' => $typeId,
            'project_description' => $projectDescription,
            'preferred_date' => $preferredDate,
            'preferred_time' => $preferredTime,
            'urgency' => $urgency,
        ],
    ]);
    return;
}

$bookingId = ConsultationBooking::create([
    'tenant_id' => $tenantId,
    'type_id' => $typeId,
    'customer_name' => sanitize($name),
    'customer_email' => $email,
    'customer_phone' => sanitize($phone),
    'company' => sanitize($company),
    'project_description' => sanitize($projectDescription),
    'preferred_date' => $preferredDate,
    'preferred_time' => $preferredTime,
    'urgency' => $urgency,
    'status' => 'pending',
]);

$booking = ConsultationBooking::find($bookingId, $tenantId);

// Send confirmation email to customer
try {
    $companyName = $tenant['company_name'] ?? $tenant['name'] ?? 'Kompaza';
    $typeName = $booking['type_name'] ?? 'Consultation';
    $timeLabels = ['morning' => 'Morning (9:00 - 12:00)', 'afternoon' => 'Afternoon (12:00 - 17:00)', 'evening' => 'Evening (17:00 - 20:00)'];
    $timeLabel = $timeLabels[$preferredTime] ?? $preferredTime;

    $htmlContent = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #1f2937;">Booking Confirmation</h2>
        <p>Hi ' . htmlspecialchars($name) . ',</p>
        <p>Thank you for booking a consultation with <strong>' . htmlspecialchars($companyName) . '</strong>. We have received your request and will get back to you shortly.</p>
        <div style="background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0 0 8px 0;"><strong>Booking Reference:</strong> ' . htmlspecialchars($booking['booking_number']) . '</p>
            <p style="margin: 0 0 8px 0;"><strong>Consultation Type:</strong> ' . htmlspecialchars($typeName) . '</p>
            <p style="margin: 0 0 8px 0;"><strong>Preferred Date:</strong> ' . date('d M Y', strtotime($preferredDate)) . '</p>
            <p style="margin: 0 0 8px 0;"><strong>Preferred Time:</strong> ' . htmlspecialchars($timeLabel) . '</p>
            <p style="margin: 0;"><strong>Urgency:</strong> ' . ucfirst($urgency) . '</p>
        </div>
        <p>We will confirm the exact date and time via email. If you have any questions, feel free to reply to this email.</p>
        <p style="color: #6b7280; font-size: 13px; margin-top: 30px;">Best regards,<br>' . htmlspecialchars($companyName) . '</p>
    </div>';

    $emailService = EmailServiceFactory::create($tenant);
    $emailService->sendTransactionalEmail(
        $email,
        'Booking Confirmation - ' . $booking['booking_number'],
        $htmlContent
    );
} catch (\Exception $e) {
    error_log("Consultation confirmation email failed: " . $e->getMessage());
}

// Notify admin
try {
    $adminEmail = $tenant['contact_email'] ?? $tenant['email'] ?? null;
    if ($adminEmail) {
        $companyName = $tenant['company_name'] ?? $tenant['name'] ?? 'Kompaza';
        $urgencyColors = ['low' => '#9ca3af', 'medium' => '#f59e0b', 'high' => '#ef4444'];
        $urgencyColor = $urgencyColors[$urgency] ?? '#9ca3af';

        $adminHtml = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
            <h2 style="color: #1f2937;">New Consultation Booking</h2>
            <p>A new consultation has been booked on your platform.</p>
            <div style="background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <p style="margin: 0 0 8px 0;"><strong>Reference:</strong> ' . htmlspecialchars($booking['booking_number']) . '</p>
                <p style="margin: 0 0 8px 0;"><strong>Customer:</strong> ' . htmlspecialchars($name) . ' (' . htmlspecialchars($email) . ')</p>
                <p style="margin: 0 0 8px 0;"><strong>Company:</strong> ' . htmlspecialchars($company ?: 'N/A') . '</p>
                <p style="margin: 0 0 8px 0;"><strong>Type:</strong> ' . htmlspecialchars($booking['type_name'] ?? 'N/A') . '</p>
                <p style="margin: 0 0 8px 0;"><strong>Preferred Date:</strong> ' . date('d M Y', strtotime($preferredDate)) . '</p>
                <p style="margin: 0 0 8px 0;"><strong>Urgency:</strong> <span style="color: ' . $urgencyColor . '; font-weight: bold;">' . ucfirst($urgency) . '</span></p>
                ' . ($projectDescription ? '<p style="margin: 0;"><strong>Description:</strong> ' . htmlspecialchars($projectDescription) . '</p>' : '') . '
            </div>
            <p style="color: #6b7280; font-size: 13px;">Manage this booking in your admin panel: /admin/consultations</p>
        </div>';

        $emailService = EmailServiceFactory::create($tenant);
        $emailService->sendTransactionalEmail(
            $adminEmail,
            'New Consultation Booking - ' . $booking['booking_number'],
            $adminHtml
        );
    }
} catch (\Exception $e) {
    error_log("Consultation admin notification email failed: " . $e->getMessage());
}

logAudit('consultation_booking_created', 'consultation_booking', $bookingId);
flashMessage('success', 'Your consultation has been booked successfully!');
redirect('/consultation/success?ref=' . urlencode($booking['booking_number']));
