<?php
$pageTitle = 'Checkout';
$tenant = currentTenant();
$metaDescription = 'Complete your order';
$currency = $tenant['currency'] ?? 'DKK';
$taxRate = (float)($tenant['tax_rate'] ?? 25);

ob_start();
?>

<section class="py-12 lg:py-16" x-data="checkoutPage()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Checkout</h1>

        <!-- Empty cart redirect -->
        <template x-if="items.length === 0">
            <div class="text-center py-16 bg-white rounded-xl border border-gray-200">
                <p class="text-gray-500 text-lg mb-4">Your cart is empty. Add some products first.</p>
                <a href="/produkter" class="btn-brand inline-flex items-center px-6 py-3 text-white font-semibold rounded-lg transition text-sm">
                    Browse Products
                </a>
            </div>
        </template>

        <template x-if="items.length > 0">
            <form action="/checkout" method="POST" @submit.prevent="submitOrder()">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left: Customer Info -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Contact Information -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-6">Contact Information</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="customer_name" x-model="form.name" required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                                           placeholder="John Smith">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                                    <input type="email" name="customer_email" x-model="form.email" required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                                           placeholder="john@company.com">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                    <input type="tel" name="customer_phone" x-model="form.phone"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                                           placeholder="+45 12 34 56 78">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                                    <input type="text" name="customer_company" x-model="form.company"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                                           placeholder="Company name (optional)">
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-6">Shipping Address</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Street Address <span class="text-red-500">*</span></label>
                                    <input type="text" name="shipping_street" x-model="form.shipping.street" required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                                           placeholder="123 Main Street">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">City <span class="text-red-500">*</span></label>
                                    <input type="text" name="shipping_city" x-model="form.shipping.city" required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                                           placeholder="Copenhagen">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code <span class="text-red-500">*</span></label>
                                    <input type="text" name="shipping_postal" x-model="form.shipping.postal" required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                                           placeholder="2100">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Country <span class="text-red-500">*</span></label>
                                    <input type="text" name="shipping_country" x-model="form.shipping.country" required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                                           placeholder="Denmark">
                                </div>
                            </div>
                        </div>

                        <!-- Billing Address -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-lg font-bold text-gray-900">Billing Address</h2>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="sameAsShipping" class="w-4 h-4 rounded border-gray-300 text-brand focus:ring-brand">
                                    <span class="text-sm text-gray-600">Same as shipping</span>
                                </label>
                            </div>
                            <div x-show="!sameAsShipping" x-transition class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                                    <input type="text" name="billing_street" x-model="form.billing.street"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                    <input type="text" name="billing_city" x-model="form.billing.city"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                                    <input type="text" name="billing_postal" x-model="form.billing.postal"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                    <input type="text" name="billing_country" x-model="form.billing.country"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent">
                                </div>
                            </div>
                            <div x-show="sameAsShipping" class="text-sm text-gray-500">
                                Using the same address as shipping.
                            </div>
                        </div>

                        <!-- Order Bumps -->
                        <?php if (!empty($orderBumps)): ?>
                        <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl border-2 border-dashed border-yellow-300 p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <h2 class="text-lg font-bold text-gray-900">Special Add-ons</h2>
                            </div>
                            <div class="space-y-3">
                                <?php foreach ($orderBumps as $bump): ?>
                                <label class="flex items-start gap-3 p-4 bg-white rounded-lg border border-yellow-200 cursor-pointer hover:border-yellow-400 transition"
                                       x-data="{ checked: false }">
                                    <input type="checkbox" name="order_bumps[]" value="<?= (int)$bump['id'] ?>"
                                           x-model="checked"
                                           @change="checked ? addBump(<?= (int)$bump['id'] ?>, <?= (float)$bump['bump_price_dkk'] ?>, '<?= h(addslashes($bump['product_name'])) ?>') : removeBump(<?= (int)$bump['id'] ?>)"
                                           class="mt-1 w-4 h-4 rounded border-gray-300 text-brand focus:ring-brand flex-shrink-0">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-semibold text-gray-900">
                                                <?= h($bump['display_text'] ?: 'Add ' . $bump['product_name'] . ' for only ' . formatMoney($bump['bump_price_dkk'])) ?>
                                            </span>
                                            <span class="text-sm font-bold text-brand ml-2"><?= formatMoney($bump['bump_price_dkk']) ?></span>
                                        </div>
                                        <?php if ($bump['description']): ?>
                                            <p class="text-xs text-gray-500 mt-1"><?= h($bump['description']) ?></p>
                                        <?php endif; ?>
                                        <?php if ((float)$bump['bump_price_dkk'] < (float)$bump['product_price']): ?>
                                            <p class="text-xs text-green-600 font-medium mt-1">Save <?= formatMoney($bump['product_price'] - $bump['bump_price_dkk']) ?> (normally <?= formatMoney($bump['product_price']) ?>)</p>
                                        <?php endif; ?>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Payment Plan Option -->
                        <?php if ($hasPaymentPlan && $paymentPlanInfo): ?>
                        <div class="bg-white rounded-xl border border-gray-200 p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-6">Payment Option</h2>
                            <div class="space-y-3">
                                <label class="flex items-center p-4 border rounded-lg cursor-pointer transition"
                                       :class="form.payment_plan === 'full' ? 'border-brand bg-blue-50 ring-2 ring-brand' : 'border-gray-200 hover:border-gray-300'">
                                    <input type="radio" name="payment_plan" value="full" x-model="form.payment_plan" class="w-4 h-4 text-brand focus:ring-brand">
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-gray-900">Pay in Full</span>
                                        <p class="text-xs text-gray-500 mt-0.5">One-time payment of <span x-text="formatPrice(total)"></span></p>
                                    </div>
                                </label>
                                <label class="flex items-center p-4 border rounded-lg cursor-pointer transition"
                                       :class="form.payment_plan === 'installment' ? 'border-brand bg-blue-50 ring-2 ring-brand' : 'border-gray-200 hover:border-gray-300'">
                                    <input type="radio" name="payment_plan" value="installment" x-model="form.payment_plan" class="w-4 h-4 text-brand focus:ring-brand">
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-gray-900"><?= (int)$paymentPlanInfo['installment_count'] ?> Monthly Payments</span>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            <?= formatMoney($paymentPlanInfo['installment_price']) ?>/month
                                            <?php if ($paymentPlanInfo['trial_days'] > 0): ?>
                                                &middot; <?= (int)$paymentPlanInfo['trial_days'] ?>-day free trial
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Payment Method -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-6">Payment Method</h2>
                            <div class="space-y-3">
                                <label class="flex items-center p-4 border rounded-lg cursor-pointer transition"
                                       :class="form.payment_method === 'invoice' ? 'border-brand bg-blue-50 ring-2 ring-brand' : 'border-gray-200 hover:border-gray-300'">
                                    <input type="radio" name="payment_method" value="invoice" x-model="form.payment_method" class="w-4 h-4 text-brand focus:ring-brand">
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-gray-900">Invoice / Bank Transfer</span>
                                        <p class="text-xs text-gray-500 mt-0.5">Pay within 14 days via bank transfer</p>
                                    </div>
                                </label>
                                <label class="flex items-center p-4 border rounded-lg cursor-pointer transition"
                                       :class="form.payment_method === 'card' ? 'border-brand bg-blue-50 ring-2 ring-brand' : 'border-gray-200 hover:border-gray-300'">
                                    <input type="radio" name="payment_method" value="card" x-model="form.payment_method" class="w-4 h-4 text-brand focus:ring-brand">
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-gray-900">Credit / Debit Card</span>
                                        <p class="text-xs text-gray-500 mt-0.5">Pay securely with Stripe</p>
                                    </div>
                                </label>
                            </div>
                            <div x-show="form.payment_method === 'card'" x-cloak class="mt-4">
                                <div id="stripe-card-element" class="p-4 border border-gray-300 rounded-lg bg-gray-50 min-h-[44px]">
                                    <p class="text-sm text-gray-400">Secure payment processing will appear here.</p>
                                </div>
                            </div>
                            <p class="mt-3 text-xs text-gray-400">
                                <svg class="w-3.5 h-3.5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Your payment information is encrypted and secure.
                            </p>
                        </div>
                    </div>

                    <!-- Right: Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-xl border border-gray-200 p-6 sticky top-24">
                            <h2 class="text-lg font-bold text-gray-900 mb-6">Order Summary</h2>

                            <!-- Items -->
                            <div class="space-y-3 mb-6">
                                <template x-for="item in items" :key="item.id">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0 bg-gray-50 border border-gray-100">
                                            <template x-if="item.image">
                                                <img :src="item.image" class="w-full h-full object-cover" alt="">
                                            </template>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate" x-text="item.name"></p>
                                            <p class="text-xs text-gray-500">Qty: <span x-text="item.qty"></span></p>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900" x-text="formatPrice(item.price * item.qty)"></span>
                                    </div>
                                </template>
                            </div>

                            <!-- Discount Code -->
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <div class="flex gap-2">
                                    <input type="text" x-model="discountCode" placeholder="Discount code"
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                                           :disabled="discountApplied">
                                    <button type="button" @click="applyDiscount()" :disabled="discountApplied || !discountCode"
                                            class="px-4 py-2 text-sm font-medium rounded-lg transition"
                                            :class="discountApplied ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                                        <span x-text="discountApplied ? 'Applied' : 'Apply'"></span>
                                    </button>
                                </div>
                                <p x-show="discountError" x-cloak class="text-xs text-red-600 mt-1" x-text="discountError"></p>
                                <div x-show="discountApplied" x-cloak class="flex items-center justify-between mt-2 text-sm">
                                    <span class="text-green-600 font-medium" x-text="discountLabel"></span>
                                    <button type="button" @click="removeDiscount()" class="text-xs text-red-500 hover:text-red-700">Remove</button>
                                </div>
                            </div>

                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between text-gray-600">
                                    <span>Subtotal</span>
                                    <span x-text="formatPrice(subtotal)"></span>
                                </div>
                                <template x-for="bump in selectedBumps" :key="bump.id">
                                    <div class="flex justify-between text-yellow-700">
                                        <span class="truncate mr-2" x-text="'+ ' + bump.name"></span>
                                        <span x-text="formatPrice(bump.price)"></span>
                                    </div>
                                </template>
                                <div x-show="discountAmount > 0" x-cloak class="flex justify-between text-green-600">
                                    <span>Discount</span>
                                    <span x-text="'-' + formatPrice(discountAmount)"></span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Tax (<?= h($taxRate) ?>%)</span>
                                    <span x-text="formatPrice(tax)"></span>
                                </div>
                                <div class="border-t border-gray-200 pt-3 flex justify-between text-gray-900 font-bold text-base">
                                    <span>Total</span>
                                    <span x-text="formatPrice(total)"></span>
                                </div>
                            </div>

                            <!-- Error -->
                            <div x-show="error" x-cloak class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" x-text="error"></div>

                            <button type="submit" :disabled="submitting"
                                    class="mt-6 w-full btn-brand px-6 py-3.5 text-white font-semibold rounded-lg transition text-base disabled:opacity-50">
                                <span x-show="!submitting">Place Order</span>
                                <span x-show="submitting" x-cloak>Processing...</span>
                            </button>

                            <a href="/kurv" class="mt-3 block text-center text-sm text-gray-500 hover:text-gray-700 transition py-2">
                                Back to Cart
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </template>
    </div>
</section>

<script>
function checkoutPage() {
    return {
        items: [],
        selectedBumps: [],
        currency: '<?= h($currency) ?>',
        taxRate: <?= $taxRate ?>,
        sameAsShipping: true,
        submitting: false,
        error: '',
        discountCode: '',
        discountAmount: 0,
        discountApplied: false,
        discountLabel: '',
        discountError: '',
        emailSaved: false,
        form: {
            name: '<?= h(currentUser()['name'] ?? '') ?>',
            email: '<?= h(currentUser()['email'] ?? '') ?>',
            phone: '',
            company: '',
            payment_method: 'invoice',
            payment_plan: 'full',
            shipping: { street: '', city: '', postal: '', country: '' },
            billing: { street: '', city: '', postal: '', country: '' }
        },

        init() {
            this.items = JSON.parse(localStorage.getItem('kz_cart_<?= (int)$tenant['id'] ?>') || '[]');

            // Save email for abandoned cart recovery when email is entered
            this.$watch('form.email', (val) => {
                if (val && val.includes('@') && !this.emailSaved) {
                    this.saveEmailForRecovery();
                }
            });
        },

        get subtotal() {
            const itemsTotal = this.items.reduce((sum, item) => sum + item.price * item.qty, 0);
            const bumpsTotal = this.selectedBumps.reduce((sum, b) => sum + b.price, 0);
            return itemsTotal + bumpsTotal;
        },

        get discountedSubtotal() {
            return Math.max(0, this.subtotal - this.discountAmount);
        },

        get tax() {
            return this.discountedSubtotal * (this.taxRate / 100);
        },

        get total() {
            return this.discountedSubtotal + this.tax;
        },

        addBump(id, price, name) {
            if (!this.selectedBumps.find(b => b.id === id)) {
                this.selectedBumps.push({ id, price, name });
            }
        },

        removeBump(id) {
            this.selectedBumps = this.selectedBumps.filter(b => b.id !== id);
        },

        formatPrice(amount) {
            return amount.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' ' + this.currency;
        },

        async saveEmailForRecovery() {
            try {
                await fetch('/api/cart/save-email', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: this.form.email, name: this.form.name })
                });
                this.emailSaved = true;
            } catch (e) { /* silent */ }
        },

        async applyDiscount() {
            if (!this.discountCode.trim()) return;
            this.discountError = '';
            try {
                const res = await fetch('/api/discount/validate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ code: this.discountCode, subtotal: this.subtotal })
                });
                const data = await res.json();
                if (data.valid) {
                    this.discountApplied = true;
                    this.discountAmount = data.discount_amount;
                    this.discountLabel = data.label || 'Discount applied';
                } else {
                    this.discountError = data.error || 'Invalid discount code.';
                }
            } catch (err) {
                this.discountError = 'Could not validate discount code.';
            }
        },

        removeDiscount() {
            this.discountApplied = false;
            this.discountAmount = 0;
            this.discountLabel = '';
            this.discountCode = '';
            this.discountError = '';
        },

        async submitOrder() {
            this.submitting = true;
            this.error = '';

            const billing = this.sameAsShipping ? this.form.shipping : this.form.billing;

            try {
                const response = await fetch('/checkout/submit', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        csrf_token: '<?= generateCsrfToken() ?>',
                        customer_name: this.form.name,
                        customer_email: this.form.email,
                        customer_phone: this.form.phone,
                        customer_company: this.form.company,
                        payment_method: this.form.payment_method,
                        payment_plan: this.form.payment_plan,
                        discount_code: this.discountApplied ? this.discountCode : null,
                        shipping_address: this.form.shipping,
                        billing_address: billing,
                        items: this.items,
                        order_bumps: this.selectedBumps.map(b => b.id)
                    })
                });
                const data = await response.json();
                if (data.success) {
                    localStorage.removeItem('kz_cart_<?= (int)$tenant['id'] ?>');
                    window.dispatchEvent(new Event('cart-updated'));
                    window.location.href = data.redirect || '/account/orders';
                } else {
                    this.error = data.message || 'Something went wrong. Please try again.';
                }
            } catch (err) {
                this.error = 'Network error. Please try again.';
            } finally {
                this.submitting = false;
            }
        }
    };
}
</script>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
