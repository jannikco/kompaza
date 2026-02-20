<?php
$isConnected = $tenant && $tenant['stripe_connect_id'];
$isOnboarded = $tenant && $tenant['stripe_connect_onboarded'];
$chargesEnabled = $tenant && $tenant['stripe_connect_charges_enabled'];
$payoutsEnabled = $tenant && $tenant['stripe_connect_payouts_enabled'];
?>

<div class="max-w-2xl">
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        <div class="flex items-center space-x-4 mb-6">
            <div class="w-12 h-12 bg-purple-600/20 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-white">Stripe Connect</h2>
                <p class="text-sm text-gray-400">Modtag betalinger for dine e-bøger</p>
            </div>
        </div>

        <!-- Status -->
        <div class="space-y-3 mb-6">
            <div class="flex items-center justify-between py-2 border-b border-gray-700">
                <span class="text-gray-400">Stripe-konto</span>
                <span class="flex items-center <?= $isConnected ? 'text-green-400' : 'text-gray-500' ?>">
                    <?php if ($isConnected): ?>
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Oprettet
                    <?php else: ?>
                        Ikke oprettet
                    <?php endif; ?>
                </span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-700">
                <span class="text-gray-400">Onboarding</span>
                <span class="flex items-center <?= $isOnboarded ? 'text-green-400' : 'text-yellow-400' ?>">
                    <?= $isOnboarded ? 'Fuldført' : 'Ikke fuldført' ?>
                </span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-700">
                <span class="text-gray-400">Kan modtage betalinger</span>
                <span class="flex items-center <?= $chargesEnabled ? 'text-green-400' : 'text-red-400' ?>">
                    <?= $chargesEnabled ? 'Ja' : 'Nej' ?>
                </span>
            </div>
            <div class="flex items-center justify-between py-2">
                <span class="text-gray-400">Kan modtage udbetalinger</span>
                <span class="flex items-center <?= $payoutsEnabled ? 'text-green-400' : 'text-red-400' ?>">
                    <?= $payoutsEnabled ? 'Ja' : 'Nej' ?>
                </span>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex space-x-3">
            <?php if (!$isConnected || !$isOnboarded): ?>
                <a href="/admin/stripe-connect/forbind"
                    class="px-6 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-medium text-sm">
                    <?= $isConnected ? 'Fortsæt onboarding' : 'Forbind med Stripe' ?>
                </a>
            <?php endif; ?>
            <?php if ($isOnboarded): ?>
                <a href="/admin/stripe-connect/dashboard"
                    class="px-6 py-2.5 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition font-medium text-sm">
                    Åbn Stripe Dashboard
                </a>
            <?php endif; ?>
        </div>

        <?php if ($chargesEnabled): ?>
        <div class="mt-6 p-4 bg-green-500/10 border border-green-500/20 rounded-lg">
            <p class="text-green-400 text-sm">
                Din konto er fuldt konfigureret. Kunder kan nu købe dine e-bøger, og betalinger overføres automatisk til din Stripe-konto.
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>
