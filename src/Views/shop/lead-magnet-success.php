<?php
$pageTitle = 'Thank You';
$tenant = currentTenant();
$metaDescription = 'Thank you for your download request.';

ob_start();
?>

<section class="py-20 lg:py-28">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-10 sm:p-14">
            <!-- Success Icon -->
            <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">Thank You!</h1>
            <p class="text-lg text-gray-500 mb-2">Your download is on its way.</p>
            <p class="text-gray-500 mb-8">
                Check your email inbox for the download link. It should arrive within the next few minutes.
                If you don't see it, be sure to check your spam folder.
            </p>

            <div class="bg-gray-50 rounded-xl p-6 border border-gray-100 mb-8">
                <div class="flex items-center justify-center gap-3 text-gray-600">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-sm font-medium">Check your email for the download link</span>
                </div>
            </div>

            <a href="/" class="inline-flex items-center justify-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-900 font-semibold rounded-lg transition text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Home
            </a>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
