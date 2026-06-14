<?php
$pageTitle = 'Checkout - ' . h($productName);
$tenant = currentTenant();
$metaDescription = 'Complete your purchase';
$currency = $tenant['currency'] ?? 'DKK';
$taxRate = (float)($tenant['tax_rate'] ?? 25);

ob_start();
?>

<section class="py-12 lg:py-16" x-data="paymentLinkCheckout()">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="/pay/checkout" @submit.prevent="submitOrder()">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="token" value="<?= h($link['token']) ?>">

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                <!-- Left: Form -->
                <div class="lg:col-span-3 space-y-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Complete Your Purchase</h1>
                        <p class="text-sm text-gray-500 mt-1">Secure checkout</p>
                    </div>

                    <!-- Product Preview -->
                    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
                        <?php if ($product['image_path']): ?>
                            <img src="<?= h(imageUrl($product['image_path'])) ?>" class="w-16 h-16 rounded-lg object-cover" alt="">
                        <?php endif; ?>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900"><?= h($productName) ?></h3>
                            <?php if ($product['short_description']): ?>
                                <p class="text-sm text-gray-500 mt-0.5"><?= h($product['short_description']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-900"><?= formatMoney($price) ?></p>
                            <?php if ($link['custom_price_dkk'] && (float)$link['custom_price_dkk'] < (float)$product['price_dkk']): ?>
                                <p class="text-sm text-gray-500 line-through"><?= formatMoney($product['price_dkk']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                        <h2 class="text-lg font-bold text-gray-900">Your Details</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="form.name" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                <input type="email" name="email" x-model="form.email" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <input type="tel" name="phone" x-model="form.phone"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                                <input type="text" name="company" x-model="form.company"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <?php if ($link['allow_quantity']): ?>
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                        <input type="number" name="quantity" x-model.number="quantity" min="1" max="100"
                               class="w-24 px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent">
                    </div>
                    <?php endif; ?>

                    <!-- Payment Method -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Payment Method</h2>
                        <div class="space-y-3">
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer transition"
                                   :class="form.payment_method === 'invoice' ? 'border-brand bg-blue-50 ring-2 ring-brand' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="payment_method" value="invoice" x-model="form.payment_method" class="w-4 h-4 text-brand focus:ring-brand">
                                <div class="ml-3">
                                    <span class="text-sm font-medium text-gray-900">Invoice / Bank Transfer</span>
                                </div>
                            </label>
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer transition"
                                   :class="form.payment_method === 'card' ? 'border-brand bg-blue-50 ring-2 ring-brand' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="payment_method" value="card" x-model="form.payment_method" class="w-4 h-4 text-brand focus:ring-brand">
                                <div class="ml-3">
                                    <span class="text-sm font-medium text-gray-900">Credit / Debit Card</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Right: Summary -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl border border-gray-200 p-6 sticky top-24">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Order Summary</h2>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span><?= h($productName) ?> <span x-show="quantity > 1" x-cloak>x <span x-text="quantity"></span></span></span>
                                <span x-text="formatPrice(<?= (float)$price ?> * quantity)"></span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Tax (<?= h($taxRate) ?>%)</span>
                                <span x-text="formatPrice(<?= (float)$price ?> * quantity * <?= $taxRate / 100 ?>)"></span>
                            </div>
                            <div class="border-t border-gray-200 pt-3 flex justify-between text-gray-900 font-bold text-base">
                                <span>Total</span>
                                <span x-text="formatPrice(<?= (float)$price ?> * quantity * <?= 1 + $taxRate / 100 ?>)"></span>
                            </div>
                        </div>

                        <div x-show="error" x-cloak class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" x-text="error"></div>

                        <button type="submit" :disabled="submitting"
                                class="mt-6 w-full btn-brand px-6 py-3.5 text-white font-semibold rounded-lg transition text-base disabled:opacity-50">
                            <span x-show="!submitting">Pay Now</span>
                            <span x-show="submitting" x-cloak>Processing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
function paymentLinkCheckout() {
    return {
        quantity: 1,
        submitting: false,
        error: '',
        form: {
            name: '<?= h(currentUser()['name'] ?? '') ?>',
            email: '<?= h(currentUser()['email'] ?? '') ?>',
            phone: '',
            company: '',
            payment_method: 'invoice'
        },
        formatPrice(amount) {
            return amount.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' <?= h($currency) ?>';
        },
        submitOrder() {
            this.submitting = true;
            this.error = '';
            this.$el.closest('form').submit();
        }
    };
}
</script>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
