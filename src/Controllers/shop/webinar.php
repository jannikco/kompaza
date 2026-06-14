<?php

use App\Models\Webinar;
use App\Models\WebinarRegistration;

$tenant = currentTenant();
$tenantId = currentTenantId();

$webinar = Webinar::findBySlug($slug, $tenantId);
if (!$webinar || $webinar['status'] === 'draft') {
    http_response_code(404);
    view('errors/404');
    exit;
}

$alreadyRegistered = false;
if (!empty($_SESSION['webinar_registered_' . $webinar['id']])) {
    $alreadyRegistered = true;
}

$bulletPoints = $webinar['bullet_points'] ? json_decode($webinar['bullet_points'], true) : [];

// Determine which page to show
$showRoom = in_array($webinar['status'], ['live', 'replay']);

view('shop/webinar', [
    'tenant' => $tenant,
    'webinar' => $webinar,
    'alreadyRegistered' => $alreadyRegistered,
    'bulletPoints' => $bulletPoints,
    'showRoom' => $showRoom,
]);
