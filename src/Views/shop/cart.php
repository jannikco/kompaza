<?php
$pageTitle = 'Shopping Cart';
$tenant = currentTenant();
$metaDescription = 'Your shopping cart';
$currency = $tenant['currency'] ?? 'DKK';
$taxRate = (float)($tenant['tax_rate'] ?? 25);

ob_start();
?>

<section class="py-12 lg:py-16" x-data="cartPage()">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

        <!-- Empty State -->
        <template x-if="items.length === 0">
            <div class="text-center py-16 bg-white rounded-xl border border-gray-200">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                <p class="text-gray-500 text-lg mb-2">Your cart is empty</p>
                <p class="text-gray-400 text-sm mb-6">Browse our products and add items to your cart.</p>
                <a href="/produkter" class="btn-brand inline-flex items-center px-6 py-3 text-white font-semibold rounded-lg transition text-sm">
                    Browse Products
                </a>
            </div>
        </template>

        <!-- Cart with items -->
        <template x-if="items.length > 0">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2 space-y-4">
                    <template x-for="(item, index) in items" :key="item.id">
                        <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6 flex items-start gap-4">
                            <!-- Image -->
                            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-lg overflow-hidden flex-shrink-0 bg-gray-50 border border-gray-100">
                                <template x-if="item.image">
                                    <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!item.image">
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                </template>
                            </div>

                            <!-- Details -->
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 text-sm sm:text-base truncate" x-text="item.name"></h3>
                                <p class="text-sm text-gray-500 mt-1" x-text="formatPrice(item.price)"></p>

                                <!-- Quantity Controls -->
                                <div class="flex items-center gap-3 mt-3">
                                    <div class="flex items-center border border-gray-300 rounded-lg">
                                        <button @click="updateQty(index, -1)" class="px-2.5 py-1.5 text-gray-600 hover:text-gray-900 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                        </button>
                                        <span class="px-3 py-1.5 text-sm font-medium border-x border-gray-300 min-w-[2.5rem] text-center" x-text="item.qty"></span>
                                        <button @click="updateQty(index, 1)" class="px-2.5 py-1.5 text-gray-600 hover:text-gray-900 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        </button>
                                    </div>
                                    <button @click="removeItem(index)" class="text-red-500 hover:text-red-700 text-sm font-medium transition">
                                        Remove
                                    </button>
                                </div>
                            </div>

                            <!-- Line Total -->
                            <div class="text-right flex-shrink-0">
                                <span class="font-semibold text-gray-900" x-text="formatPrice(item.price * item.qty)"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl border border-gray-200 p-6 sticky top-24">
                        <h2 class="text-lg font-bold text-gray-900 mb-6">Order Summary</h2>
                        <div class="space-y-3 text-sm">
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
                        <a href="/checkout"
                           class="mt-6 block w-full btn-brand px-6 py-3.5 text-white font-semibold rounded-lg transition text-center text-base">
                            Proceed to Checkout
                        </a>
                        <a href="/produkter" class="mt-3 block w-full text-center text-sm text-gray-500 hover:text-gray-700 transition py-2">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </template>
    </div>
</section>

<script>
function cartPage() {
    return {
        items: [],
        currency: '<?= h($currency) ?>',
        taxRate: <?= $taxRate ?>,

        init() {
            this.loadCart();
            window.addEventListener('cart-updated', () => this.loadCart());
        },

        loadCart() {
            this.items = JSON.parse(localStorage.getItem('kz_cart_<?= (int)$tenant['id'] ?>') || '[]');
        },

        saveCart() {
            localStorage.setItem('kz_cart_<?= (int)$tenant['id'] ?>', JSON.stringify(this.items));
            window.dispatchEvent(new Event('cart-updated'));
        },

        updateQty(index, delta) {
            this.items[index].qty += delta;
            if (this.items[index].qty < 1) {
                this.items.splice(index, 1);
            }
            this.saveCart();
        },

        removeItem(index) {
            this.items.splice(index, 1);
            this.saveCart();
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
        }
    };
}
</script>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
