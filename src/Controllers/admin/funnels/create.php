<?php

use App\Models\LeadMagnet;
use App\Models\Product;
use App\Models\EmailSequence;

$tenantId = currentTenantId();

$leadMagnets = LeadMagnet::allByTenant($tenantId, 'published');
$products = Product::allByTenant($tenantId, 'active');
$emailSequences = EmailSequence::allByTenant($tenantId);

view('admin/funnels/form', [
    'tenant' => currentTenant(),
    'funnel' => null,
    'steps' => [],
    'leadMagnets' => $leadMagnets,
    'products' => $products,
    'emailSequences' => $emailSequences,
]);
