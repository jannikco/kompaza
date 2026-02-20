<?php
$pageTitle = 'Forgot Password';
$tenant = currentTenant();
$metaDescription = 'Reset your password';

ob_start();
?>

<section class="py-16 lg:py-24">
    <div class="max-w-md mx-auto px-4 sm:px-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Forgot Password</h1>
                <p class="mt-2 text-sm text-gray-500">Enter your email and we'll send you a reset link</p>
            </div>

            <form action="/forgot-password" method="POST" class="space-y-5">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" required autofocus
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                           placeholder="you@example.com">
                </div>

                <button type="submit"
                        class="w-full btn-brand px-6 py-3 text-white font-semibold rounded-lg transition text-base">
                    Send Reset Link
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="/login" class="text-sm font-medium text-brand hover:underline">Back to Login</a>
            </div>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
