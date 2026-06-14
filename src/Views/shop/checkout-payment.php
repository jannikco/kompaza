<?php
$pageTitle = 'Complete Payment';
$metaDescription = 'Complete your payment';
$currency = $tenant['currency'] ?? 'DKK';

ob_start();
?>

<section class="py-12 lg:py-20 bg-gray-50 min-h-screen">
    <div class="max-w-lg mx-auto px-4 sm:px-6" x-data="paymentPage()">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Complete Your Payment</h1>
            <p class="text-gray-500 mt-2">Order #<?= h($order['order_number']) ?></p>
        </div>

        <!-- Order Summary -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm text-gray-600">Total Amount</span>
                <span class="text-2xl font-bold text-gray-900"><?= formatMoney($order['total_dkk']) ?></span>
            </div>
            <div class="text-xs text-gray-500">
                <div class="flex justify-between"><span>Subtotal</span><span><?= formatMoney($order['subtotal_dkk']) ?></span></div>
                <div class="flex justify-between"><span>Tax</span><span><?= formatMoney($order['tax_dkk']) ?></span></div>
                <?php if ((float)$order['discount_dkk'] > 0): ?>
                <div class="flex justify-between text-green-600"><span>Discount</span><span>-<?= formatMoney($order['discount_dkk']) ?></span></div>
                <?php endif; ?>
            </div>
            <?php if ($order['payment_plan_type'] === 'installment' && $order['installment_count']): ?>
            <div class="mt-3 pt-3 border-t border-gray-200">
                <p class="text-sm text-indigo-600 font-medium">
                    Payment 1 of <?= (int)$order['installment_count'] ?> installments
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Stripe Payment Element -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <div id="payment-element" class="mb-4">
                <!-- Stripe Payment Element will be mounted here -->
            </div>

            <!-- Error message -->
            <div x-show="error" x-cloak class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm mb-4" x-text="error"></div>

            <button type="button" @click="handlePayment()" :disabled="processing"
                    class="w-full btn-brand px-6 py-3.5 text-white font-semibold rounded-lg transition text-base disabled:opacity-50">
                <span x-show="!processing">Pay <?= formatMoney($order['total_dkk']) ?></span>
                <span x-show="processing" x-cloak>Processing...</span>
            </button>
        </div>

        <div class="text-center">
            <p class="text-xs text-gray-400">
                <svg class="w-3.5 h-3.5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Payments are processed securely by Stripe. We never store your card details.
            </p>
            <div class="flex items-center justify-center gap-4 mt-4 text-gray-400">
                <span class="text-xs">Accepts:</span>
                <span class="text-xs font-medium">Visa</span>
                <span class="text-xs font-medium">Mastercard</span>
                <span class="text-xs font-medium">Apple Pay</span>
                <span class="text-xs font-medium">Google Pay</span>
            </div>
        </div>
    </div>
</section>

<script src="https://js.stripe.com/v3/"></script>
<script>
function paymentPage() {
    return {
        stripe: null,
        elements: null,
        processing: false,
        error: '',

        init() {
            const stripeKey = '<?= h($stripePublishableKey) ?>';
            if (!stripeKey) {
                this.error = 'Payment system is not configured. Please contact support.';
                return;
            }

            this.stripe = Stripe(stripeKey);

            const appearance = {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#4F46E5',
                    borderRadius: '8px',
                }
            };

            this.elements = this.stripe.elements({
                clientSecret: '<?= h($clientSecret) ?>',
                appearance: appearance,
            });

            const paymentElement = this.elements.create('payment', {
                layout: 'tabs',
            });
            paymentElement.mount('#payment-element');
        },

        async handlePayment() {
            if (this.processing) return;
            this.processing = true;
            this.error = '';

            try {
                const { error } = await this.stripe.confirmPayment({
                    elements: this.elements,
                    confirmParams: {
                        return_url: window.location.origin + '/checkout/payment-success?order_id=<?= (int)$order['id'] ?>',
                    },
                });

                // This point is reached only if there's an immediate error
                if (error) {
                    if (error.type === 'card_error' || error.type === 'validation_error') {
                        this.error = error.message;
                    } else {
                        this.error = 'An unexpected error occurred. Please try again.';
                    }
                }
            } catch (err) {
                this.error = 'Payment failed. Please try again.';
            } finally {
                this.processing = false;
            }
        }
    };
}
</script>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
