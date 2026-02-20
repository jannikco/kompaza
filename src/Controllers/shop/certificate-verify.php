<?php

use App\Models\Certificate;

$tenant = currentTenant();
$certNumber = $slug ?? '';

$certificate = null;
if (!empty($certNumber)) {
    $certificate = Certificate::findByNumber($certNumber);
}

view('shop/certificate-verify', [
    'tenant' => $tenant,
    'certificate' => $certificate,
    'certNumber' => $certNumber,
]);
