<?php

use App\Models\ContactMessage;

$tenantId = currentTenantId();
$messages = ContactMessage::allByTenant($tenantId);

view('admin/contact-messages/index', [
    'tenant' => currentTenant(),
    'messages' => $messages,
]);
