<?php
$currentPlan = $subscription ?? null;
$isActive = $currentPlan && in_array($currentPlan['status'], ['active', 'trialing']);
$isCanceling = $currentPlan && $currentPlan['cancel_at_period_end'];
?>

<!-- Current plan status -->
<?php if ($currentPlan): ?>
<div class="mb-8 bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Dit abonnement</h2>
        <span class="px-3 py-1 rounded-full text-sm font-medium
            <?php if ($currentPlan['status'] === 'active'): ?>bg-green-100 text-green-700
            <?php elseif ($currentPlan['status'] === 'trialing'): ?>bg-blue-100 text-blue-700
            <?php elseif ($currentPlan['status'] === 'past_due'): ?>bg-yellow-100 text-yellow-700
            <?php else: ?>bg-red-100 text-red-700<?php endif; ?>">
            <?php
            $statusLabels = [
                'active' => 'Aktiv',
                'trialing' => 'Prøveperiode',
                'past_due' => 'Forfaldent',
                'canceled' => 'Annulleret',
                'unpaid' => 'Ubetalt',
                'incomplete' => 'Ufuldstændig',
            ];
            echo $statusLabels[$currentPlan['status']] ?? ucfirst($currentPlan['status']);
            ?>
        </span>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
        <div>
            <span class="text-gray-500">Plan:</span>
            <span class="text-gray-900 ml-2 font-medium"><?= h($currentPlan['plan_name'] ?? 'Ukendt') ?></span>
        </div>
        <div>
            <span class="text-gray-500">Interval:</span>
            <span class="text-gray-900 ml-2"><?= $currentPlan['billing_interval'] === 'annual' ? 'Årligt' : 'Månedligt' ?></span>
        </div>
        <?php if ($currentPlan['current_period_end']): ?>
        <div>
            <span class="text-gray-500"><?= $isCanceling ? 'Udløber:' : 'Fornyes:' ?></span>
            <span class="text-gray-900 ml-2"><?= formatDate($currentPlan['current_period_end']) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <div class="mt-4 flex space-x-3">
        <?php if ($isActive && $currentPlan['stripe_customer_id']): ?>
            <a href="/admin/abonnement/portal" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 border border-gray-200 transition text-sm">
                Administrer betaling
            </a>
        <?php endif; ?>
        <?php if ($isActive && !$isCanceling && $currentPlan['stripe_subscription_id']): ?>
            <a href="/admin/abonnement/annuller" class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition text-sm"
               onclick="return confirm('Er du sikker på at du vil annullere dit abonnement?')">
                Annuller
            </a>
        <?php elseif ($isCanceling): ?>
            <a href="/admin/abonnement/genoptag" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm">
                Genoptag abonnement
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Pricing table -->
<div class="mb-8" x-data="{ interval: 'monthly' }">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-900"><?= $currentPlan ? 'Skift plan' : 'Vælg en plan' ?></h2>
        <div class="flex items-center bg-gray-100 rounded-lg p-1 border border-gray-200">
            <button @click="interval = 'monthly'"
                :class="interval === 'monthly' ? 'bg-blue-600 text-white' : 'text-gray-500'"
                class="px-4 py-1.5 rounded-md text-sm font-medium transition">Månedligt</button>
            <button @click="interval = 'annual'"
                :class="interval === 'annual' ? 'bg-blue-600 text-white' : 'text-gray-500'"
                class="px-4 py-1.5 rounded-md text-sm font-medium transition">Årligt <span class="text-green-600 text-xs">Spar 18%</span></button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($plans as $plan): ?>
        <?php
            $isCurrent = $currentPlan && $currentPlan['plan_id'] == $plan['id'] && $isActive;
            $monthlyPrice = $plan['price_monthly_usd'] / 100;
            $annualPrice = $plan['price_annual_usd'] / 100;
        ?>
        <div class="bg-white rounded-xl border shadow-sm <?= $plan['slug'] === 'growth' ? 'border-blue-500' : 'border-gray-200' ?> p-6 relative">
            <?php if ($plan['slug'] === 'growth'): ?>
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full">POPULÆR</div>
            <?php endif; ?>

            <h3 class="text-xl font-bold text-gray-900 mb-2"><?= h($plan['name']) ?></h3>
            <div class="mb-4">
                <span class="text-3xl font-bold text-gray-900" x-show="interval === 'monthly'">$<?= number_format($monthlyPrice, 0) ?></span>
                <span class="text-3xl font-bold text-gray-900" x-show="interval === 'annual'" x-cloak>$<?= number_format($annualPrice, 0) ?></span>
                <span class="text-gray-500 text-sm">/md</span>
            </div>

            <ul class="space-y-2 mb-6 text-sm text-gray-600">
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <?= $plan['max_customers'] ? number_format($plan['max_customers']) . ' kunder' : 'Ubegrænset kunder' ?>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <?= $plan['max_lead_magnets'] ? $plan['max_lead_magnets'] . ' lead magnets' : 'Ubegrænset lead magnets' ?>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <?= $plan['max_products'] ? $plan['max_products'] . ' produkter' : 'Ubegrænset produkter' ?>
                </li>
            </ul>

            <?php if ($isCurrent): ?>
                <div class="w-full py-2.5 text-center bg-gray-100 text-gray-700 rounded-lg text-sm font-medium">
                    Nuværende plan
                </div>
            <?php else: ?>
                <form method="POST" action="/admin/abonnement/checkout">
                    <?= csrfField() ?>
                    <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                    <input type="hidden" name="interval" x-bind:value="interval">
                    <button type="submit"
                        class="w-full py-2.5 <?= $plan['slug'] === 'growth' ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' ?> rounded-lg text-sm font-medium transition">
                        <?= $currentPlan ? 'Skift til ' . h($plan['name']) : 'Start gratis prøveperiode' ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Invoice history -->
<?php if (!empty($invoices)): ?>
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Fakturaer</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm border-b border-gray-200">
                    <th class="px-6 py-3">Dato</th>
                    <th class="px-6 py-3">Beløb</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Faktura</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $invoice): ?>
                <tr class="border-b border-gray-200/50">
                    <td class="px-6 py-3 text-sm text-gray-600"><?= formatDate($invoice['created_at']) ?></td>
                    <td class="px-6 py-3 text-sm text-gray-900">$<?= number_format($invoice['amount_cents'] / 100, 2) ?></td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            <?= $invoice['status'] === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                            <?= $invoice['status'] === 'paid' ? 'Betalt' : ucfirst($invoice['status']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-3 text-sm">
                        <?php if ($invoice['invoice_url']): ?>
                            <a href="<?= h($invoice['invoice_url']) ?>" target="_blank" class="text-blue-600 hover:text-blue-300">Se faktura</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
