<?php
$pageTitle = 'Log In';
$tenant = currentTenant();
$metaDescription = 'Log in to your account';

ob_start();
?>

<section class="py-16 lg:py-24">
    <div class="max-w-md mx-auto px-4 sm:px-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Welcome Back</h1>
                <p class="mt-2 text-sm text-gray-500">Log in to your account</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="mb-6 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST" class="space-y-5">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" required autofocus
                           value="<?= h($email ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                           placeholder="you@example.com">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <a href="/forgot-password" class="text-sm text-brand hover:underline">Forgot password?</a>
                    </div>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                           placeholder="Your password">
                </div>

                <button type="submit"
                        class="w-full btn-brand px-6 py-3 text-white font-semibold rounded-lg transition text-base">
                    Log In
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">
                    Don't have an account?
                    <a href="/register" class="font-medium text-brand hover:underline">Create one</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
