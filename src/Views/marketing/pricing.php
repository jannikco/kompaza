<!-- Pricing Hero -->
<section class="relative overflow-hidden hero-gradient py-20">
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-cyan-500/20 rounded-full blur-3xl"></div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4 tracking-tight">
            Simple, transparent pricing
        </h1>
        <p class="text-blue-100/90 text-lg max-w-2xl mx-auto">
            Start free for 14 days. No credit card required. Upgrade, downgrade, or cancel anytime.
        </p>
    </div>

    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
            <path d="M0 60V30C240 0 480 10 720 20C960 30 1200 50 1440 30V60H0Z" fill="#f9fafb"/>
        </svg>
    </div>
</section>

<!-- Pricing Cards -->
<section class="py-20 bg-gray-50" x-data="{ annual: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Billing toggle -->
        <div class="flex items-center justify-center mb-14">
            <span class="text-sm font-medium" :class="annual ? 'text-gray-400' : 'text-gray-900'">Monthly</span>
            <button @click="annual = !annual" class="relative mx-4 w-14 h-7 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    :class="annual ? 'bg-indigo-600' : 'bg-gray-300'">
                <span class="absolute top-0.5 left-0.5 w-6 h-6 bg-white rounded-full shadow transition-transform duration-200"
                      :class="annual ? 'translate-x-7' : 'translate-x-0'"></span>
            </button>
            <span class="text-sm font-medium" :class="annual ? 'text-gray-900' : 'text-gray-400'">
                Annual
                <span class="inline-flex items-center ml-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Save ~17%</span>
            </span>
        </div>

        <?php if (empty($plans)): ?>
            <div class="text-center py-12">
                <p class="text-gray-500 text-lg">Pricing plans are being configured. Please check back soon.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-<?= count($plans) ?> gap-8 max-w-5xl mx-auto items-start">
                <?php foreach ($plans as $index => $plan):
                    $isPopular = ($plan['slug'] === 'growth');
                    $monthlyPrice = (int)$plan['price_monthly_usd'];
                    $yearlyPrice = $plan['price_yearly_usd'] ? (int)$plan['price_yearly_usd'] : null;
                    $yearlyMonthly = $yearlyPrice ? round($yearlyPrice / 12) : null;
                ?>
                    <div class="relative bg-white rounded-2xl border <?= $isPopular ? 'border-indigo-300 shadow-xl shadow-indigo-100/50 ring-2 ring-indigo-500' : 'border-gray-200 shadow-sm' ?> overflow-hidden">
                        <?php if ($isPopular): ?>
                            <div class="bg-indigo-600 text-white text-center py-2 text-sm font-semibold">
                                Most Popular
                            </div>
                        <?php endif; ?>

                        <div class="p-8">
                            <h3 class="text-xl font-bold text-gray-900 mb-2"><?= h($plan['name']) ?></h3>

                            <!-- Monthly price -->
                            <div x-show="!annual">
                                <div class="flex items-baseline mb-1">
                                    <span class="text-4xl font-extrabold text-gray-900">$<?= number_format($monthlyPrice) ?></span>
                                    <span class="text-gray-500 ml-2 text-sm">/month</span>
                                </div>
                            </div>

                            <!-- Annual price -->
                            <div x-show="annual" x-cloak>
                                <?php if ($yearlyMonthly): ?>
                                    <div class="flex items-baseline mb-1">
                                        <span class="text-4xl font-extrabold text-gray-900">$<?= number_format($yearlyMonthly) ?></span>
                                        <span class="text-gray-500 ml-2 text-sm">/month</span>
                                    </div>
                                    <p class="text-sm text-gray-400 mb-1">Billed as $<?= number_format((int)$yearlyPrice) ?>/year</p>
                                <?php else: ?>
                                    <div class="flex items-baseline mb-1">
                                        <span class="text-4xl font-extrabold text-gray-900">Custom</span>
                                    </div>
                                    <p class="text-sm text-gray-400 mb-1">Contact us for annual pricing</p>
                                <?php endif; ?>
                            </div>

                            <p class="text-gray-500 text-sm mt-3 mb-6">
                                <?php if ($plan['slug'] === 'starter'): ?>
                                    Perfect for solopreneurs and small teams getting started.
                                <?php elseif ($plan['slug'] === 'growth'): ?>
                                    For growing businesses that need the full toolkit.
                                <?php else: ?>
                                    For agencies and larger teams with advanced needs.
                                <?php endif; ?>
                            </p>

                            <a href="/register<?= $plan['slug'] !== 'enterprise' ? '?plan=' . h($plan['slug']) : '' ?>"
                               class="block w-full text-center px-6 py-3 rounded-lg font-semibold text-sm transition duration-200 <?= $isPopular
                                   ? 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm shadow-indigo-600/25'
                                   : ($plan['slug'] === 'enterprise'
                                       ? 'bg-gray-900 hover:bg-gray-800 text-white'
                                       : 'bg-gray-100 hover:bg-gray-200 text-gray-900') ?>">
                                <?= $plan['slug'] === 'enterprise' ? 'Contact Sales' : 'Start Free Trial' ?>
                            </a>

                            <!-- Features list -->
                            <?php
                                $planFeatures = [
                                    'starter' => [
                                        ['label' => '5 Products', 'included' => true],
                                        ['label' => '100 Contacts', 'included' => true],
                                        ['label' => '1 Website', 'included' => true],
                                        ['label' => 'Blog & articles', 'included' => true],
                                        ['label' => 'Lead magnets & landing pages', 'included' => true],
                                        ['label' => 'E-book publishing', 'included' => true],
                                        ['label' => 'Email marketing', 'included' => true],
                                        ['label' => 'Online courses', 'included' => false],
                                        ['label' => 'LinkedIn automation', 'included' => false],
                                        ['label' => 'Payment processing', 'included' => false],
                                    ],
                                    'growth' => [
                                        ['label' => '100 Products', 'included' => true],
                                        ['label' => '500 Contacts', 'included' => true],
                                        ['label' => '1 Website', 'included' => true],
                                        ['label' => 'Online courses', 'included' => true],
                                        ['label' => 'Blog & articles', 'included' => true],
                                        ['label' => 'Lead magnets & landing pages', 'included' => true],
                                        ['label' => 'E-book publishing', 'included' => true],
                                        ['label' => 'Email marketing', 'included' => true],
                                        ['label' => 'LinkedIn automation', 'included' => true],
                                        ['label' => 'Payment processing', 'included' => true],
                                    ],
                                    'enterprise' => [
                                        ['label' => 'Unlimited products', 'included' => true],
                                        ['label' => 'Unlimited contacts', 'included' => true],
                                        ['label' => '1 Website', 'included' => true],
                                        ['label' => 'Online courses', 'included' => true],
                                        ['label' => 'Blog & articles', 'included' => true],
                                        ['label' => 'Lead magnets & landing pages', 'included' => true],
                                        ['label' => 'E-book publishing', 'included' => true],
                                        ['label' => 'Email marketing', 'included' => true],
                                        ['label' => 'LinkedIn automation', 'included' => true],
                                        ['label' => 'Payment processing', 'included' => true],
                                        ['label' => 'Custom domain', 'included' => true],
                                        ['label' => 'Priority support', 'included' => true],
                                    ],
                                ];
                                $currentFeatures = $planFeatures[$plan['slug']] ?? $planFeatures['starter'];
                            ?>
                            <div class="mt-8 pt-8 border-t border-gray-100">
                                <p class="text-sm font-semibold text-gray-900 mb-4">What's included:</p>
                                <ul class="space-y-3">
                                    <?php foreach ($currentFeatures as $feature): ?>
                                        <li class="flex items-start text-sm">
                                            <?php if ($feature['included']): ?>
                                                <svg class="w-5 h-5 text-indigo-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                <span class="text-gray-600"><?= h($feature['label']) ?></span>
                                            <?php else: ?>
                                                <svg class="w-5 h-5 text-gray-300 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                                <span class="text-gray-400"><?= h($feature['label']) ?></span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- FAQ teaser -->
        <div class="mt-20 text-center">
            <p class="text-gray-500 text-sm">
                All plans include a 14-day free trial. No credit card required.
                <br>
                Have questions? <a href="mailto:support@kompaza.com" class="text-indigo-600 hover:text-indigo-700 font-medium">Contact our sales team</a>.
            </p>
        </div>
    </div>
</section>
