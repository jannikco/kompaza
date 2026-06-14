<?php

use App\Models\CountdownTimer;

header('Content-Type: application/json');

$tenantId = currentTenantId();
$id = (int)($_GET['id'] ?? 0);

$timer = CountdownTimer::find($id, $tenantId);
if (!$timer || $timer['status'] !== 'active') {
    echo json_encode(['error' => 'Timer not found']);
    exit;
}

$response = [
    'id' => (int)$timer['id'],
    'type' => $timer['timer_type'],
    'headline' => $timer['headline'] ?: null,
    'subheadline' => $timer['subheadline'] ?: null,
    'expired_action' => $timer['expired_action'],
    'expired_message' => $timer['expired_message'] ?: null,
    'redirect_url' => $timer['redirect_url'] ?: null,
    'style_preset' => $timer['style_preset'],
    'bg_color' => $timer['bg_color'],
    'text_color' => $timer['text_color'],
    'accent_color' => $timer['accent_color'],
];

if ($timer['timer_type'] === 'fixed') {
    $response['end_timestamp'] = strtotime($timer['end_date']) * 1000; // JS timestamp
} else {
    $response['duration_ms'] = (int)$timer['duration_minutes'] * 60 * 1000;
}

echo json_encode($response);
