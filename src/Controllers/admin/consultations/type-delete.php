<?php

use App\Models\ConsultationBooking;

if (!isPost()) redirect('/admin/consultations/types');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/consultations/types');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/consultations/types');

$type = ConsultationBooking::findType($id, $tenantId);
if (!$type) {
    flashMessage('error', 'Consultation type not found.');
    redirect('/admin/consultations/types');
}

ConsultationBooking::deleteType($id, $tenantId);

logAudit('consultation_type_deleted', 'consultation_type', $id);
flashMessage('success', 'Consultation type deleted successfully.');
redirect('/admin/consultations/types');
