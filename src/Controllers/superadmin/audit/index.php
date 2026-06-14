<?php

/**
 * Superadmin — Global Audit Log viewer.
 *
 * Lists audit_logs across ALL tenants, newest-first, with filtering and
 * pagination. Platform-wide: uses a raw cross-tenant connection (no tenant
 * scoping). Auth is enforced by the router (Auth::requireSuperAdmin).
 */

$db = \App\Database\Database::getConnection();

// ---------------------------------------------------------------------------
// Filters (GET) — sanitised, then bound as prepared parameters.
// ---------------------------------------------------------------------------
$filters = [
    'action'    => trim((string)($_GET['action'] ?? '')),
    'tenant_id' => trim((string)($_GET['tenant_id'] ?? '')),
    'user_id'   => trim((string)($_GET['user_id'] ?? '')),
    'date_from' => trim((string)($_GET['date_from'] ?? '')),
    'date_to'   => trim((string)($_GET['date_to'] ?? '')),
];

$where  = [];
$params = [];

if ($filters['action'] !== '') {
    $where[] = 'a.action LIKE :action';
    $params[':action'] = '%' . $filters['action'] . '%';
}

if ($filters['tenant_id'] !== '' && ctype_digit($filters['tenant_id'])) {
    $where[] = 'a.tenant_id = :tenant_id';
    $params[':tenant_id'] = (int)$filters['tenant_id'];
}

if ($filters['user_id'] !== '' && ctype_digit($filters['user_id'])) {
    $where[] = 'a.user_id = :user_id';
    $params[':user_id'] = (int)$filters['user_id'];
}

// Date filters: accept YYYY-MM-DD (from <input type="date">) and expand to
// full-day bounds so the range is inclusive.
if ($filters['date_from'] !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date_from'])) {
    $where[] = 'a.created_at >= :date_from';
    $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
}

if ($filters['date_to'] !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date_to'])) {
    $where[] = 'a.created_at <= :date_to';
    $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// ---------------------------------------------------------------------------
// Pagination.
// ---------------------------------------------------------------------------
$perPage = 50;
$page    = isset($_GET['page']) && ctype_digit((string)$_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset  = ($page - 1) * $perPage;

// Total count for pagination (same filters, no joins needed for the count).
$countSql  = "SELECT COUNT(*) FROM audit_logs a $whereSql";
$countStmt = $db->prepare($countSql);
$countStmt->execute($params);
$totalRows  = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));

// Clamp page if filters reduced the result set below the requested page.
if ($page > $totalPages) {
    $page   = $totalPages;
    $offset = ($page - 1) * $perPage;
}

// ---------------------------------------------------------------------------
// Main query — newest first, with actor + tenant names.
// ---------------------------------------------------------------------------
$sql = "
    SELECT
        a.id,
        a.tenant_id,
        a.user_id,
        a.action,
        a.entity_type,
        a.entity_id,
        a.ip_address,
        a.created_at,
        u.name  AS actor_name,
        u.email AS actor_email,
        u.role  AS actor_role,
        t.name  AS tenant_name,
        t.slug  AS tenant_slug
    FROM audit_logs a
    LEFT JOIN users   u ON u.id = a.user_id
    LEFT JOIN tenants t ON t.id = a.tenant_id
    $whereSql
    ORDER BY a.created_at DESC, a.id DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------------------------------------------------------
// Filter dropdown data: all tenants (for the tenant filter select).
// ---------------------------------------------------------------------------
$tenants = $db->query("SELECT id, name, slug FROM tenants ORDER BY name ASC")
              ->fetchAll(PDO::FETCH_ASSOC);

// Range summary for the pagination footer.
$rangeStart = $totalRows === 0 ? 0 : ($offset + 1);
$rangeEnd   = min($offset + $perPage, $totalRows);

view('superadmin/audit/index', [
    'logs'       => $logs,
    'tenants'    => $tenants,
    'filters'    => $filters,
    'page'       => $page,
    'perPage'    => $perPage,
    'totalRows'  => $totalRows,
    'totalPages' => $totalPages,
    'rangeStart' => $rangeStart,
    'rangeEnd'   => $rangeEnd,
]);
