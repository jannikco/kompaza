<?php
$pageTitle = 'My Certificates';
$tenant = currentTenant();
$metaDescription = 'Your earned certificates';

ob_start();
?>

<section class="py-12 lg:py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="/konto" class="text-sm text-brand hover:underline">&larr; Back to Account</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">My Certificates</h1>
        </div>

        <?php if (empty($certificates)): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                <div class="w-16 h-16 mx-auto rounded-full bg-gray-100 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                <p class="text-gray-500 mb-4">You haven't earned any certificates yet.</p>
                <a href="/courses" class="btn-brand px-6 py-2.5 text-white font-medium rounded-lg transition text-sm">Browse Courses</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <?php foreach ($certificates as $cert): ?>
                <div class="bg-white rounded-xl border border-gray-200 p-6 <?= $cert['revoked_at'] ? 'opacity-50' : '' ?>">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-900"><?= h($cert['course_title']) ?></h3>
                            <p class="text-xs text-gray-500 font-mono mt-1"><?= h($cert['certificate_number']) ?></p>
                        </div>
                        <?php if ($cert['revoked_at']): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Revoked</span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Valid</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($cert['score_percentage']): ?>
                        <p class="text-sm text-gray-600 mb-2">Score: <?= number_format($cert['score_percentage'], 1) ?>%</p>
                    <?php endif; ?>
                    <p class="text-sm text-gray-500 mb-4">Issued <?= formatDate($cert['issued_at']) ?></p>
                    <?php if (!$cert['revoked_at']): ?>
                    <div class="flex items-center space-x-3">
                        <a href="/certificate/download?id=<?= $cert['id'] ?>" target="_blank" class="text-sm font-medium text-brand hover:underline">Download</a>
                        <a href="/certificate/verify/<?= h($cert['certificate_number']) ?>" target="_blank" class="text-sm font-medium text-gray-500 hover:text-gray-700">Verify</a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
