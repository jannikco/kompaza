<?php
$pageTitle = 'Booking Confirmed';
$tenant = currentTenant();
$metaDescription = 'Your consultation booking has been received';
ob_start();
?>

<section class="py-16 lg:py-24">
    <div class="max-w-xl mx-auto px-4 sm:px-6 text-center">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 sm:p-12">
            <!-- Success Icon -->
            <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-3">Booking Received!</h1>
            <p class="text-gray-600 mb-6">
                Thank you for your consultation request. We have received your booking and will confirm the exact date and time via email shortly.
            </p>

            <?php if ($bookingRef): ?>
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <p class="text-sm text-gray-500 mb-1">Your booking reference</p>
                <p class="text-lg font-bold text-gray-900 font-mono"><?= h($bookingRef) ?></p>
            </div>
            <?php endif; ?>

            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-8 text-left">
                <h3 class="text-sm font-semibold text-blue-900 mb-2">What happens next?</h3>
                <ul class="text-sm text-blue-800 space-y-2">
                    <li class="flex items-start">
                        <svg class="w-4 h-4 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        You will receive a confirmation email with your booking details.
                    </li>
                    <li class="flex items-start">
                        <svg class="w-4 h-4 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        We will review your request and confirm the consultation time.
                    </li>
                    <li class="flex items-start">
                        <svg class="w-4 h-4 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        You will receive a final confirmation once the booking is approved.
                    </li>
                </ul>
            </div>

            <a href="/"
               class="inline-flex items-center btn-brand px-6 py-3 text-white font-semibold rounded-lg transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Home
            </a>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
