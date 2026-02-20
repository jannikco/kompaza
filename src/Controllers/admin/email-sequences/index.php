<?php

use App\Models\EmailSequence;

$tenantId = currentTenantId();
$sequences = EmailSequence::allByTenant($tenantId);

// Enrich each sequence with step count and enrollment count
$db = \App\Database\Database::getConnection();
foreach ($sequences as &$seq) {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM email_sequence_steps WHERE sequence_id = ?");
    $stmt->execute([$seq['id']]);
    $seq['step_count'] = $stmt->fetch()['count'];

    $stmt = $db->prepare("SELECT COUNT(*) as count FROM email_sequence_enrollments WHERE sequence_id = ?");
    $stmt->execute([$seq['id']]);
    $seq['enrollment_count'] = $stmt->fetch()['count'];
}
unset($seq);

view('admin/email-sequences/index', [
    'tenant' => currentTenant(),
    'sequences' => $sequences,
]);
