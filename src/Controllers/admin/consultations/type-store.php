<?php

use App\Models\ConsultationBooking;

if (!isPost()) redirect('/admin/consultations/types');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/consultations/types');
}

$tenantId = currentTenantId();
$name = sanitize($_POST['name'] ?? '');

if (!$name) {
    flashMessage('error', 'Name is required.');
    redirect('/admin/consultations/types');
}

$id = ConsultationBooking::createType([
    'tenant_id' => $tenantId,
    'name' => $name,
    'description' => sanitize($_POST['description'] ?? ''),
    'duration_minutes' => (int)($_POST['duration_minutes'] ?? 60),
    'price_dkk' => (float)($_POST['price_dkk'] ?? 0),
    'status' => sanitize($_POST['status'] ?? 'active'),
    'sort_order' => (int)($_POST['sort_order'] ?? 0),
]);

logAudit('consultation_type_created', 'consultation_type', $id);
flashMessage('success', 'Consultation type created successfully.');
redirect('/admin/consultations/types');
