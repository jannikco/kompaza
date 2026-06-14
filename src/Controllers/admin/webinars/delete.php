<?php

use App\Models\Webinar;

$tenantId = currentTenantId();
$id = (int)($_POST['id'] ?? 0);

$webinar = Webinar::find($id, $tenantId);
if (!$webinar) {
    flashMessage('error', 'Webinar not found.');
    redirect('/admin/webinars');
}

Webinar::delete($id, $tenantId);
flashMessage('success', 'Webinar deleted successfully.');
redirect('/admin/webinars');
