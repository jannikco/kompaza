<?php
/**
 * Simple workshop watch unlock page.
 * GET /workshop/watch?track=creator-os
 */

$track = preg_replace('/[^a-z0-9\-]/', '', strtolower($_GET['track'] ?? 'creator-os'));
if (!in_array($track, ['office-os', 'creator-os', 'founder-os'], true)) {
    $track = 'creator-os';
}

$unlocked = !empty($_COOKIE['kz_workshop_' . $track]);
$titles = [
    'office-os' => 'Office OS Workshop',
    'creator-os' => 'Creator OS Workshop',
    'founder-os' => 'Founder OS Workshop',
];

$pageTitle = $titles[$track] ?? 'Workshop';
$tenant = currentTenant();

// Prefer custom page if imported; else simple unlock UI
if (!$unlocked) {
    flashMessage('error', 'Please opt in to unlock the workshop.');
    redirect('/' . $track);
}

// Redirect to native course sales or custom workshop page
// If a custom page `workshop` exists users already saw it; show CTA to course
view('shop/workshop-watch', [
    'tenant' => $tenant,
    'track' => $track,
    'pageTitle' => $pageTitle,
    'titles' => $titles,
]);
