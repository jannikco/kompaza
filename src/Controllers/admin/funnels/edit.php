<?php

use App\Models\Funnel;
use App\Models\FunnelStep;
use App\Models\LeadMagnet;
use App\Models\Product;
use App\Models\EmailSequence;

$tenantId = currentTenantId();
$id = (int)($_GET['id'] ?? 0);

$funnel = Funnel::find($id, $tenantId);
if (!$funnel) {
    flashMessage('error', 'Funnel not found.');
    redirect('/admin/funnels');
}

$steps = FunnelStep::allByFunnel($id);
$leadMagnets = LeadMagnet::allByTenant($tenantId, 'published');
$products = Product::allByTenant($tenantId, 'active');
$emailSequences = EmailSequence::allByTenant($tenantId);

view('admin/funnels/form', [
    'tenant' => currentTenant(),
    'funnel' => $funnel,
    'steps' => $steps,
    'leadMagnets' => $leadMagnets,
    'products' => $products,
    'emailSequences' => $emailSequences,
]);
