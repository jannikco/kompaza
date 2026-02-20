<?php

use App\Models\Newsletter;

$tenantId = currentTenantId();
$newsletter = null;

// If editing an existing draft, load it
$id = $_GET['id'] ?? null;
if ($id) {
    $newsletter = Newsletter::find($id, $tenantId);
    if (!$newsletter) {
        flashMessage('error', 'Newsletter not found.');
        redirect('/admin/newsletters');
    }
}

$pageTitle = $newsletter ? 'Edit Newsletter' : 'Compose Newsletter';
$currentPage = 'newsletters';

view('admin/newsletters/compose', compact('newsletter', 'pageTitle', 'currentPage'));
