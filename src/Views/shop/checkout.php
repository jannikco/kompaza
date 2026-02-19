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
                <a href="/products" class="btn-brand inline-flex items-center px-6 py-3 text-white font-semibold rounded-lg transition text-sm">
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

                        <!-- Payment -->
                        <div class="bg-white rounded-xl border border-gray-200 p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-6">Payment</h2>
                            <div id="stripe-card-element" class="p-4 border border-gray-300 rounded-lg bg-gray-50 min-h-[44px]">
                                <!-- Stripe Elements will be mounted here -->
                                <p class="text-sm text-gray-400">Secure payment processing will appear here.</p>
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

                            <div class="border-t border-gray-200 pt-4 space-y-3 text-sm">
                                <div class="flex justify-between text-gray-600">
                                    <span>Subtotal</span>
                                    <span x-text="formatPrice(subtotal)"></span>
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

                            <a href="/cart" class="mt-3 block text-center text-sm text-gray-500 hover:text-gray-700 transition py-2">
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
        currency: '<?= h($currency) ?>',
        taxRate: <?= $taxRate ?>,
        sameAsShipping: true,
        submitting: false,
        error: '',
        form: {
            name: '<?= h(currentUser()['name'] ?? '') ?>',
            email: '<?= h(currentUser()['email'] ?? '') ?>',
            phone: '',
            company: '',
            shipping: { street: '', city: '', postal: '', country: '' },
            billing: { street: '', city: '', postal: '', country: '' }
        },

        init() {
            this.items = JSON.parse(localStorage.getItem('kz_cart_<?= (int)$tenant['id'] ?>') || '[]');
        },

        get subtotal() {
            return this.items.reduce((sum, item) => sum + item.price * item.qty, 0);
        },

        get tax() {
            return this.subtotal * (this.taxRate / 100);
        },

        get total() {
            return this.subtotal + this.tax;
        },

        formatPrice(amount) {
            return amount.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' ' + this.currency;
        },

        async submitOrder() {
            this.submitting = true;
            this.error = '';

            const billing = this.sameAsShipping ? this.form.shipping : this.form.billing;

            try {
                const response = await fetch('/checkout', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        csrf_token: '<?= generateCsrfToken() ?>',
                        customer_name: this.form.name,
                        customer_email: this.form.email,
                        customer_phone: this.form.phone,
                        customer_company: this.form.company,
                        shipping_address: this.form.shipping,
                        billing_address: billing,
                        items: this.items
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
