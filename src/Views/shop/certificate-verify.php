<?php
$pageTitle = 'Verify Certificate';
$tenant = currentTenant();
$metaDescription = 'Verify a certificate of completion';

ob_start();
?>

<section class="py-12 lg:py-20">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <h1 class="text-2xl font-bold text-gray-900 text-center mb-8">Certificate Verification</h1>

        <?php if ($certificate): ?>
            <?php if ($certificate['revoked_at']): ?>
                <div class="bg-red-50 border border-red-200 rounded-xl p-8 text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <h2 class="text-xl font-bold text-red-800 mb-2">Certificate Revoked</h2>
                    <p class="text-red-700">This certificate has been revoked.</p>
                    <?php if ($certificate['revocation_reason']): ?>
                        <p class="text-red-600 text-sm mt-2">Reason: <?= h($certificate['revocation_reason']) ?></p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="bg-green-50 border border-green-200 rounded-xl p-8 text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-green-100 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h2 class="text-xl font-bold text-green-800 mb-2">Valid Certificate</h2>
                    <p class="text-green-700 mb-6">This certificate is authentic and valid.</p>

                    <div class="bg-white rounded-lg p-6 text-left space-y-3 border border-green-200">
                        <div>
                            <p class="text-sm text-gray-500">Recipient</p>
                            <p class="font-semibold text-gray-900"><?= h($certificate['user_name']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Course</p>
                            <p class="font-semibold text-gray-900"><?= h($certificate['course_title']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Issued By</p>
                            <p class="font-semibold text-gray-900"><?= h($certificate['tenant_name']) ?></p>
                        </div>
                        <?php if ($certificate['score_percentage']): ?>
                        <div>
                            <p class="text-sm text-gray-500">Score</p>
                            <p class="font-semibold text-gray-900"><?= number_format($certificate['score_percentage'], 1) ?>%</p>
                        </div>
                        <?php endif; ?>
                        <div>
                            <p class="text-sm text-gray-500">Issue Date</p>
                            <p class="font-semibold text-gray-900"><?= formatDate($certificate['issued_at']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Certificate Number</p>
                            <p class="font-mono text-gray-900"><?= h($certificate['certificate_number']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php elseif (!empty($certNumber)): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-8 text-center">
                <div class="w-16 h-16 mx-auto rounded-full bg-yellow-100 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <h2 class="text-xl font-bold text-yellow-800 mb-2">Certificate Not Found</h2>
                <p class="text-yellow-700">No certificate found with number: <?= h($certNumber) ?></p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                <p class="text-gray-500">Enter a certificate number in the URL to verify it.</p>
                <p class="text-sm text-gray-400 mt-2">Example: /certificate/verify/CERT-2025-ABCD1234</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
