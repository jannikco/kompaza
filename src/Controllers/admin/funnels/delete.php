<?php

use App\Models\Funnel;

$tenantId = currentTenantId();
$id = (int)($_POST['id'] ?? 0);

$funnel = Funnel::find($id, $tenantId);
if (!$funnel) {
    flashMessage('error', 'Funnel not found.');
    redirect('/admin/funnels');
}

Funnel::delete($id, $tenantId);
flashMessage('success', 'Funnel deleted successfully.');
redirect('/admin/funnels');
