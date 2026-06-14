<?php

// Platform-wide user list across ALL tenants.
$db = \App\Database\Database::getConnection();

$search = trim($_GET['search'] ?? '');
$role = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';

$allowedRoles = ['superadmin', 'tenant_admin', 'customer'];
$allowedStatuses = ['active', 'inactive', 'banned'];

$where = [];
$params = [];

if ($search !== '') {
    $where[] = '(u.name LIKE ? OR u.email LIKE ?)';
    $term = '%' . $search . '%';
    $params[] = $term;
    $params[] = $term;
}

if (in_array($role, $allowedRoles, true)) {
    $where[] = 'u.role = ?';
    $params[] = $role;
} else {
    $role = '';
}

if (in_array($status, $allowedStatuses, true)) {
    $where[] = 'u.status = ?';
    $params[] = $status;
} else {
    $status = '';
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "
    SELECT u.id, u.name, u.email, u.role, u.status, u.tenant_id, u.last_login_at, u.created_at,
           t.name AS tenant_name, t.slug AS tenant_slug
    FROM users u
    LEFT JOIN tenants t ON t.id = u.tenant_id
    $whereSql
    ORDER BY u.created_at DESC
    LIMIT 500
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

view('superadmin/users/index', [
    'users' => $users,
    'search' => $search,
    'role' => $role,
    'status' => $status,
]);
