<?php
$pageTitle = 'Checkout — ' . $course['title'];
$tenant = $tenant ?? currentTenant();
$primaryColor = $tenant['primary_color'] ?? '#3b82f6';
ob_start();
?>

<section class="py-12 lg:py-16">
    <div class="max-w-lg mx-auto px-4 sm:px-6">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Complete Your Purchase</h1>
            <p class="mt-2 text-gray-500">You're purchasing: <strong><?= h($course['title']) ?></strong></p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
            <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100">
                <span class="text-gray-600">Course</span>
                <span class="font-medium text-gray-900"><?= h($course['title']) ?></span>
            </div>
            <div class="flex items-center justify-between text-lg font-bold">
                <span class="text-gray-900">Total</span>
                <span class="text-gray-900"><?= formatMoney($order['total_dkk']) ?></span>
            </div>
        </div>

        <div id="payment-element" class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
            <!-- Stripe Payment Element will mount here -->
        </div>

        <button id="submit-payment" class="w-full btn-brand text-white font-semibold py-3 px-6 rounded-xl transition disabled:opacity-50" disabled>
            <span id="button-text">Pay <?= formatMoney($order['total_dkk']) ?></span>
            <span id="spinner" class="hidden">Processing...</span>
        </button>

        <div id="payment-message" class="hidden mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700"></div>
    </div>
</section>

<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe = Stripe('<?= h($stripePublishableKey) ?>');
const clientSecret = '<?= h($clientSecret) ?>';

const elements = stripe.elements({ clientSecret });
const paymentElement = elements.create('payment');
paymentElement.mount('#payment-element');

paymentElement.on('ready', () => {
    document.getElementById('submit-payment').disabled = false;
});

document.getElementById('submit-payment').addEventListener('click', async () => {
    const btn = document.getElementById('submit-payment');
    btn.disabled = true;
    document.getElementById('button-text').classList.add('hidden');
    document.getElementById('spinner').classList.remove('hidden');

    const { error } = await stripe.confirmPayment({
        elements,
        confirmParams: {
            return_url: window.location.origin + '/course/<?= h($course['slug']) ?>/learn?purchased=1',
        },
    });

    if (error) {
        const msg = document.getElementById('payment-message');
        msg.textContent = error.message;
        msg.classList.remove('hidden');
        btn.disabled = false;
        document.getElementById('button-text').classList.remove('hidden');
        document.getElementById('spinner').classList.add('hidden');
    }
});
</script>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
