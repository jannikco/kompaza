<?php

use App\Models\Funnel;
use App\Models\FunnelStep;
use App\Models\LeadMagnet;
use App\Models\Product;
use App\Models\CustomPage;
use App\Models\Webinar;

$tenantId = currentTenantId();

$funnel = Funnel::findBySlug($slug, $tenantId);
if (!$funnel || $funnel['status'] !== 'active') {
    http_response_code(404);
    view('errors/404');
    exit;
}

$steps = FunnelStep::allByFunnel($funnel['id']);
if (!$steps) {
    http_response_code(404);
    view('errors/404');
    exit;
}

// Entry = first step by sort_order (allByFunnel orders ASC)
$entry = $steps[0];

Funnel::incrementViews($funnel['id']);
FunnelStep::incrementViews($entry['id']);

// Resolve the destination URL for the entry step
$dest = $entry['custom_url'] ?: null;
if (!$dest) {
    $rt = $entry['resource_type'] ?? null;
    $rid = (int)($entry['resource_id'] ?? 0);
    if ($rt && $rid) {
        switch ($rt) {
            case 'lead_magnet':
                $r = LeadMagnet::find($rid, $tenantId);
                if ($r) $dest = '/lp/' . $r['slug'];
                break;
            case 'product':
                $r = Product::find($rid, $tenantId);
                if ($r) $dest = '/produkt/' . $r['slug'];
                break;
            case 'custom_page':
                $r = CustomPage::find($rid, $tenantId);
                if ($r) $dest = '/' . $r['slug'];
                break;
            case 'webinar':
                $r = Webinar::find($rid, $tenantId);
                if ($r) $dest = '/webinar/' . $r['slug'];
                break;
        }
    }
}

redirect($dest ?: '/');
