<?php

use App\Models\ConsultationBooking;

if (!isPost()) redirect('/admin/consultations');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/consultations');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/consultations');

$booking = ConsultationBooking::find($id, $tenantId);
if (!$booking) {
    flashMessage('error', 'Booking not found.');
    redirect('/admin/consultations');
}

$status = sanitize($_POST['status'] ?? '');
$adminNotes = sanitize($_POST['admin_notes'] ?? '');

$validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
if (!in_array($status, $validStatuses)) {
    flashMessage('error', 'Invalid status.');
    redirect('/admin/consultations');
}

$updateData = ['status' => $status];
if ($adminNotes !== '') {
    $updateData['admin_notes'] = $adminNotes;
}

ConsultationBooking::update($id, $updateData);

logAudit('consultation_status_updated', 'consultation_booking', $id, ['status' => $status]);
flashMessage('success', 'Booking status updated successfully.');
redirect('/admin/consultations');
