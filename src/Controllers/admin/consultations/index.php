<?php

use App\Models\ConsultationBooking;

$tenantId = currentTenantId();
$status = sanitize($_GET['status'] ?? '');
$validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
$statusFilter = in_array($status, $validStatuses) ? $status : null;

$bookings = ConsultationBooking::allByTenant($tenantId, $statusFilter);

$counts = [
    'all' => ConsultationBooking::countByTenant($tenantId),
    'pending' => ConsultationBooking::countByTenant($tenantId, 'pending'),
    'confirmed' => ConsultationBooking::countByTenant($tenantId, 'confirmed'),
    'completed' => ConsultationBooking::countByTenant($tenantId, 'completed'),
    'cancelled' => ConsultationBooking::countByTenant($tenantId, 'cancelled'),
];

view('admin/consultations/index', [
    'tenant' => currentTenant(),
    'bookings' => $bookings,
    'statusFilter' => $statusFilter,
    'counts' => $counts,
]);
