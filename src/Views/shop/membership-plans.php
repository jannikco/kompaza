<?php
$pageTitle = 'Membership Plans';
$metaDescription = 'Choose the perfect membership plan and unlock exclusive content, courses, and community access.';
$tenant = $tenant ?? currentTenant();
$currency = $tenant['currency'] ?? 'DKK';
$plans = $plans ?? [];
$currentMembership = $currentMembership ?? null;
ob_start();
?>

<section class="py-12 lg:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="text-center max-w-3xl mx-auto mb-12">
            <h1 class="text-4xl font-extrabold text-gray-900 mb-4">Membership Plans</h1>
            <p class="text-lg text-gray-600">Choose the plan that fits your goals. Upgrade or downgrade anytime.</p>
        </div>

        <?php if (empty($plans)): ?>
            <div class="text-center py-16 bg-white rounded-xl border border-gray-200">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <p class="text-gray-500 text-lg">No membership plans are available at the moment.</p>
                <p class="text-gray-400 text-sm mt-2">Please check back soon.</p>
            </div>
        <?php else: ?>

            <!-- Billing Toggle -->
            <div x-data="membershipPlans()" x-cloak>
                <div class="flex items-center justify-center mb-10">
                    <span class="text-sm font-medium mr-3" :class="billingInterval === 'monthly' ? 'text-gray-900' : 'text-gray-500'">Monthly</span>
                    <button type="button" @click="billingInterval = billingInterval === 'monthly' ? 'yearly' : 'monthly'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                            :class="billingInterval === 'yearly' ? 'bg-brand' : 'bg-gray-300'"
                            role="switch" :aria-checked="billingInterval === 'yearly'">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                              :class="billingInterval === 'yearly' ? 'translate-x-5' : 'translate-x-0'"></span>
                    </button>
                    <span class="text-sm font-medium ml-3" :class="billingInterval === 'yearly' ? 'text-gray-900' : 'text-gray-500'">
                        Yearly
                        <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 ml-1">Save up to 17%</span>
                    </span>
                </div>

                <!-- Plan Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?= min(count($plans), 3) ?> gap-6 lg:gap-8 max-w-5xl mx-auto">
                    <?php foreach ($plans as $index => $plan): ?>
                        <?php
                            $isFree = (float)$plan['price_monthly'] == 0 && (float)$plan['price_yearly'] == 0;
                            $isCurrentPlan = $currentMembership && (int)$currentMembership['plan_id'] === (int)$plan['id'];
                            $isPopular = (int)$plan['tier_level'] === 2; // mid-tier is typically "popular"
                        ?>
                        <div class="relative bg-white rounded-2xl border-2 transition-shadow hover:shadow-lg flex flex-col <?= $isPopular ? 'border-brand shadow-md' : 'border-gray-200' ?>">

                            <?php if ($isPopular): ?>
                                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                                    <span class="inline-flex items-center rounded-full bg-brand px-4 py-1 text-xs font-bold text-white uppercase tracking-wide">Most Popular</span>
                                </div>
                            <?php endif; ?>

                            <?php if ($isCurrentPlan): ?>
                                <div class="absolute -top-3.5 right-4">
                                    <span class="inline-flex items-center rounded-full bg-green-500 px-3 py-1 text-xs font-bold text-white">Current Plan</span>
                                </div>
                            <?php endif; ?>

                            <div class="p-6 lg:p-8 flex-1 flex flex-col">
                                <!-- Plan Name & Description -->
                                <h3 class="text-xl font-bold text-gray-900 mb-2"><?= h($plan['name']) ?></h3>
                                <?php if ($plan['description']): ?>
                                    <p class="text-sm text-gray-500 mb-6 leading-relaxed"><?= h($plan['description']) ?></p>
                                <?php else: ?>
                                    <div class="mb-6"></div>
                                <?php endif; ?>

                                <!-- Price -->
                                <div class="mb-6">
                                    <?php if ($isFree): ?>
                                        <div class="flex items-baseline">
                                            <span class="text-4xl font-extrabold text-gray-900">Free</span>
                                        </div>
                                        <p class="text-sm text-gray-500 mt-1">No credit card required</p>
                                    <?php else: ?>
                                        <div x-show="billingInterval === 'monthly'">
                                            <div class="flex items-baseline">
                                                <span class="text-4xl font-extrabold text-gray-900"><?= formatMoney((int)$plan['price_monthly'] / 100, $currency) ?></span>
                                                <span class="text-sm text-gray-500 ml-2">/month</span>
                                            </div>
                                        </div>
                                        <div x-show="billingInterval === 'yearly'" x-cloak>
                                            <div class="flex items-baseline">
                                                <span class="text-4xl font-extrabold text-gray-900"><?= formatMoney((int)$plan['price_yearly'] / 100 / 12, $currency) ?></span>
                                                <span class="text-sm text-gray-500 ml-2">/month</span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">Billed <?= formatMoney((int)$plan['price_yearly'] / 100, $currency) ?> per year</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Features List -->
                                <ul class="space-y-3 mb-8 flex-1">
                                    <?php if ($plan['max_courses'] === null): ?>
                                        <li class="flex items-start gap-3">
                                            <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span class="text-sm text-gray-700">Unlimited courses</span>
                                        </li>
                                    <?php elseif ((int)$plan['max_courses'] > 0): ?>
                                        <li class="flex items-start gap-3">
                                            <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span class="text-sm text-gray-700"><?= (int)$plan['max_courses'] ?> course<?= (int)$plan['max_courses'] > 1 ? 's' : '' ?></span>
                                        </li>
                                    <?php else: ?>
                                        <li class="flex items-start gap-3">
                                            <svg class="w-5 h-5 text-gray-300 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span class="text-sm text-gray-400">No course access</span>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ($plan['max_ebooks'] === null): ?>
                                        <li class="flex items-start gap-3">
                                            <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span class="text-sm text-gray-700">Unlimited ebooks</span>
                                        </li>
                                    <?php elseif ((int)$plan['max_ebooks'] > 0): ?>
                                        <li class="flex items-start gap-3">
                                            <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span class="text-sm text-gray-700"><?= (int)$plan['max_ebooks'] ?> ebook<?= (int)$plan['max_ebooks'] > 1 ? 's' : '' ?></span>
                                        </li>
                                    <?php else: ?>
                                        <li class="flex items-start gap-3">
                                            <svg class="w-5 h-5 text-gray-300 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span class="text-sm text-gray-400">No ebook access</span>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ($plan['can_access_prompts']): ?>
                                        <li class="flex items-start gap-3">
                                            <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span class="text-sm text-gray-700">Access to prompt library</span>
                                        </li>
                                    <?php else: ?>
                                        <li class="flex items-start gap-3">
                                            <svg class="w-5 h-5 text-gray-300 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span class="text-sm text-gray-400">No prompt library access</span>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ($plan['can_post_community']): ?>
                                        <li class="flex items-start gap-3">
                                            <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span class="text-sm text-gray-700">Full community access</span>
                                        </li>
                                    <?php elseif (!$plan['community_read_only']): ?>
                                        <li class="flex items-start gap-3">
                                            <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span class="text-sm text-gray-700">Community access</span>
                                        </li>
                                    <?php else: ?>
                                        <li class="flex items-start gap-3">
                                            <svg class="w-5 h-5 text-gray-300 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span class="text-sm text-gray-400">Read-only community</span>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ($plan['can_access_live_qa']): ?>
                                        <li class="flex items-start gap-3">
                                            <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span class="text-sm text-gray-700">Live Q&amp;A sessions</span>
                                        </li>
                                    <?php else: ?>
                                        <li class="flex items-start gap-3">
                                            <svg class="w-5 h-5 text-gray-300 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span class="text-sm text-gray-400">No live sessions</span>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ((int)$plan['discount_percent'] > 0): ?>
                                        <li class="flex items-start gap-3">
                                            <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span class="text-sm text-gray-700"><?= (int)$plan['discount_percent'] ?>% discount on products</span>
                                        </li>
                                    <?php endif; ?>
                                </ul>

                                <!-- CTA Button -->
                                <?php if ($isCurrentPlan): ?>
                                    <div class="space-y-3">
                                        <div class="w-full text-center py-3 px-6 rounded-lg bg-gray-100 text-gray-500 font-semibold text-sm">
                                            Current Plan
                                        </div>
                                        <form action="/membership/portal" method="POST">
                                            <?= csrfField() ?>
                                            <button type="submit" class="w-full text-center py-2.5 px-6 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium text-sm transition">
                                                Manage Billing
                                            </button>
                                        </form>
                                    </div>
                                <?php elseif ($isFree): ?>
                                    <?php if (isAuthenticated()): ?>
                                        <span class="w-full text-center py-3 px-6 rounded-lg bg-gray-100 text-gray-500 font-semibold text-sm block">
                                            Free Plan
                                        </span>
                                    <?php else: ?>
                                        <a href="/registrer" class="w-full text-center btn-brand py-3 px-6 rounded-lg text-white font-semibold text-sm transition block">
                                            Sign Up Free
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if (isAuthenticated()): ?>
                                        <button type="button"
                                                @click="subscribe(<?= (int)$plan['id'] ?>)"
                                                :disabled="loading === <?= (int)$plan['id'] ?>"
                                                class="w-full btn-brand py-3 px-6 rounded-lg text-white font-semibold text-sm transition disabled:opacity-50 <?= $isPopular ? 'shadow-lg' : '' ?>">
                                            <span x-show="loading !== <?= (int)$plan['id'] ?>">Subscribe</span>
                                            <span x-show="loading === <?= (int)$plan['id'] ?>" x-cloak>
                                                <svg class="animate-spin h-5 w-5 mx-auto text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            </span>
                                        </button>
                                    <?php else: ?>
                                        <a href="/login?redirect=<?= urlencode('/membership') ?>" class="w-full text-center btn-brand py-3 px-6 rounded-lg text-white font-semibold text-sm transition block <?= $isPopular ? 'shadow-lg' : '' ?>">
                                            Log In to Subscribe
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Error message -->
                <div x-show="error" x-cloak x-transition class="max-w-md mx-auto mt-8">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700 text-sm text-center" x-text="error"></div>
                </div>
            </div>

        <?php endif; ?>

        <!-- FAQ / Trust signals -->
        <div class="max-w-3xl mx-auto mt-16 text-center">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-1">Secure Payment</h4>
                    <p class="text-xs text-gray-500">Powered by Stripe</p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-1">Cancel Anytime</h4>
                    <p class="text-xs text-gray-500">No long-term commitment</p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-1">Instant Access</h4>
                    <p class="text-xs text-gray-500">Start learning immediately</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function membershipPlans() {
    return {
        billingInterval: 'monthly',
        loading: null,
        error: '',

        async subscribe(planId) {
            this.loading = planId;
            this.error = '';

            try {
                const formData = new FormData();
                formData.append('plan_id', planId);
                formData.append('billing_interval', this.billingInterval);

                const response = await fetch('/membership/checkout', {
                    method: 'POST',
                    body: formData,
                });

                const data = await response.json();

                if (data.url) {
                    window.location.href = data.url;
                } else if (data.error) {
                    this.error = data.error;
                    this.loading = null;
                } else {
                    this.error = 'Something went wrong. Please try again.';
                    this.loading = null;
                }
            } catch (err) {
                this.error = 'Network error. Please try again.';
                this.loading = null;
            }
        }
    };
}
</script>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
