<?php

use App\Models\Tenant;
use App\Models\Plan;
use App\Database\Database;

$search = sanitize($_GET['search'] ?? '');

$db = Database::getConnection();

if ($search) {
    $stmt = $db->prepare("
        SELECT t.*, p.name as plan_name,
            (SELECT COUNT(*) FROM users WHERE tenant_id = t.id) as user_count
        FROM tenants t
        LEFT JOIN plans p ON t.plan_id = p.id
        WHERE t.name LIKE ? OR t.slug LIKE ? OR t.email LIKE ?
        ORDER BY t.created_at DESC
    ");
    $searchTerm = '%' . $search . '%';
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
} else {
    $stmt = $db->prepare("
        SELECT t.*, p.name as plan_name,
            (SELECT COUNT(*) FROM users WHERE tenant_id = t.id) as user_count
        FROM tenants t
        LEFT JOIN plans p ON t.plan_id = p.id
        ORDER BY t.created_at DESC
    ");
    $stmt->execute();
}

$tenants = $stmt->fetchAll();

view('superadmin/tenants/index', [
    'tenants' => $tenants,
    'search' => $search,
]);
