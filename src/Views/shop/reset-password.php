<?php
$pageTitle = 'Reset Password';
$tenant = currentTenant();
$metaDescription = 'Set a new password';

ob_start();
?>

<section class="py-16 lg:py-24">
    <div class="max-w-md mx-auto px-4 sm:px-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Reset Password</h1>
                <p class="mt-2 text-sm text-gray-500">Enter your new password below</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="mb-6 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <form action="/reset-password" method="POST" class="space-y-5">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="token" value="<?= h($token) ?>">

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input type="password" id="password" name="password" required minlength="8"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                           placeholder="Minimum 8 characters">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                           placeholder="Confirm your password">
                </div>

                <button type="submit"
                        class="w-full btn-brand px-6 py-3 text-white font-semibold rounded-lg transition text-base">
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
