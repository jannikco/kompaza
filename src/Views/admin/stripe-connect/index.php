<?php
$isConnected = $tenant && !empty($tenant['stripe_connect_id']);
$isOnboarded = $tenant && !empty($tenant['stripe_connect_onboarded']);
$chargesEnabled = $tenant && !empty($tenant['stripe_connect_charges_enabled']);
$payoutsEnabled = $tenant && !empty($tenant['stripe_connect_payouts_enabled']);
?>

<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-center space-x-4 mb-6">
            <div class="w-12 h-12 bg-purple-600/20 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Stripe Connect</h2>
                <p class="text-sm text-gray-500">Receive payouts for ebook sales on your site</p>
            </div>
        </div>

        <!-- Status -->
        <div class="space-y-3 mb-6">
            <div class="flex items-center justify-between py-2 border-b border-gray-200">
                <span class="text-gray-500">Stripe account</span>
                <span class="flex items-center <?= $isConnected ? 'text-green-600' : 'text-gray-500' ?>">
                    <?php if ($isConnected): ?>
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Created
                    <?php else: ?>
                        Not created
                    <?php endif; ?>
                </span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-200">
                <span class="text-gray-500">Onboarding</span>
                <span class="flex items-center <?= $isOnboarded ? 'text-green-600' : 'text-yellow-600' ?>">
                    <?= $isOnboarded ? 'Complete' : 'Incomplete' ?>
                </span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-200">
                <span class="text-gray-500">Can accept charges</span>
                <span class="flex items-center <?= $chargesEnabled ? 'text-green-600' : 'text-red-600' ?>">
                    <?= $chargesEnabled ? 'Yes' : 'No' ?>
                </span>
            </div>
            <div class="flex items-center justify-between py-2">
                <span class="text-gray-500">Can receive payouts</span>
                <span class="flex items-center <?= $payoutsEnabled ? 'text-green-600' : 'text-red-600' ?>">
                    <?= $payoutsEnabled ? 'Yes' : 'No' ?>
                </span>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex space-x-3">
            <?php if (!$isConnected || !$isOnboarded || !$chargesEnabled): ?>
                <a href="/admin/stripe-connect/forbind"
                    class="px-6 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-medium text-sm">
                    <?= $isConnected ? 'Continue onboarding' : 'Connect with Stripe' ?>
                </a>
            <?php endif; ?>
            <?php if ($isOnboarded): ?>
                <a href="/admin/stripe-connect/dashboard"
                    class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium text-sm">
                    Open Stripe Dashboard
                </a>
            <?php endif; ?>
        </div>

        <?php if ($chargesEnabled): ?>
        <div class="mt-6 p-4 bg-green-500/10 border border-green-500/20 rounded-lg">
            <p class="text-green-700 text-sm">
                Your account is fully configured. Customers can buy your ebooks, and payouts go to your connected Stripe account.
            </p>
        </div>
        <?php else: ?>
        <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
            <p class="text-amber-800 text-sm">
                Connect Stripe to receive payments for ebooks. Product checkout can also use your own Stripe keys under Settings.
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>
