<?php

use App\Models\Prompt;

if (!isPost()) redirect('/admin/prompts');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    redirect('/admin/prompts');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/prompts');

$prompt = Prompt::find($id, $tenantId);
if (!$prompt) {
    flashMessage('error', 'Prompt not found.');
    redirect('/admin/prompts');
}

Prompt::delete($id, $tenantId);

logAudit('prompt_deleted', 'prompt', $id);
flashMessage('success', 'Prompt deleted.');
redirect('/admin/prompts');
