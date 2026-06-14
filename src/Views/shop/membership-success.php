<?php
$pageTitle = 'Membership Activated';
$metaDescription = 'Your membership has been successfully activated.';
$tenant = $tenant ?? currentTenant();
ob_start();
?>

<meta http-equiv="refresh" content="5;url=/membership/dashboard">

<section class="py-16 lg:py-24">
    <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="bg-white rounded-2xl border border-gray-200 p-8 lg:p-12">

            <!-- Green checkmark icon -->
            <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <!-- Heading -->
            <h1 class="text-2xl font-extrabold text-gray-900 mb-3">Welcome to Your Membership!</h1>

            <!-- Description -->
            <p class="text-gray-500 mb-2">Your membership is being activated. This usually takes a few seconds.</p>
            <p class="text-gray-400 text-sm mb-8">You will be redirected to your dashboard automatically.</p>

            <!-- Loading spinner -->
            <div class="flex justify-center mb-8">
                <svg class="animate-spin h-6 w-6 text-brand" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <!-- Manual link -->
            <a href="/membership/dashboard" class="btn-brand inline-flex items-center px-6 py-3 text-white font-semibold rounded-lg transition text-sm">
                Go to My Dashboard
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>

        </div>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
