<?php

$tenantId = currentTenantId();

// Load customers for admin select
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("SELECT id, name, email FROM users WHERE tenant_id = ? AND role = 'customer' ORDER BY name");
$stmt->execute([$tenantId]);
$customers = $stmt->fetchAll();

view('admin/companies/form', [
    'tenant' => currentTenant(),
    'customers' => $customers,
    'company' => null,
]);
