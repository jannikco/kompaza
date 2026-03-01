<?php

use App\Models\PostAutomation;
use App\Models\PostComment;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$automation = PostAutomation::find($id, $tenantId);

if (!$automation) {
    http_response_code(404);
    view('errors/404');
    exit;
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

$comments = PostComment::allByAutomation($id, $perPage, $offset);
$totalComments = PostComment::countByAutomation($id);
$totalPages = max(1, ceil($totalComments / $perPage));

// Filter support
$filter = $_GET['filter'] ?? 'all';

if ($filter === 'matched') {
    $db = \App\Database\Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM connectpilot_post_comments WHERE automation_id = ? AND keyword_matched = 1 ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$id, $perPage, $offset]);
    $comments = $stmt->fetchAll();
} elseif ($filter === 'pending_dm') {
    $db = \App\Database\Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM connectpilot_post_comments WHERE automation_id = ? AND keyword_matched = 1 AND dm_sent = 0 ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$id, $perPage, $offset]);
    $comments = $stmt->fetchAll();
}

view('admin/connectpilot/automations/comments', [
    'tenant' => currentTenant(),
    'automation' => $automation,
    'comments' => $comments,
    'totalComments' => $totalComments,
    'currentPage' => $page,
    'totalPages' => $totalPages,
    'filter' => $filter,
]);
