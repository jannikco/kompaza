<?php

use App\Models\Webinar;
use App\Models\WebinarRegistration;
use App\Models\Product;
use App\Models\EmailSequence;

$tenantId = currentTenantId();
$id = (int)($_GET['id'] ?? 0);

$webinar = Webinar::find($id, $tenantId);
if (!$webinar) {
    flashMessage('error', 'Webinar not found.');
    redirect('/admin/webinars');
}

$registrations = WebinarRegistration::allByWebinar($id);
$products = Product::allByTenant($tenantId, 'active');
$emailSequences = EmailSequence::allByTenant($tenantId);

view('admin/webinars/form', [
    'tenant' => currentTenant(),
    'webinar' => $webinar,
    'registrations' => $registrations,
    'products' => $products,
    'emailSequences' => $emailSequences,
]);
