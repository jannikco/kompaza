<?php
$pageTitle = 'Create Account';
$tenant = currentTenant();
$metaDescription = 'Create a new account';

ob_start();
?>

<section class="py-16 lg:py-24">
    <div class="max-w-md mx-auto px-4 sm:px-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Create Account</h1>
                <p class="mt-2 text-sm text-gray-500">Sign up to get started</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="mb-6 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors) && is_array($errors)): ?>
                <div class="mb-6 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        <?php foreach ($errors as $err): ?>
                            <li><?= h($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="/register" method="POST" class="space-y-5">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" id="name" name="name" required autofocus
                           value="<?= h($name ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                           placeholder="John Smith">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" required
                           value="<?= h($email ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                           placeholder="you@example.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" required minlength="8"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                           placeholder="Minimum 8 characters">
                </div>

                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="8"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                           placeholder="Repeat your password">
                </div>

                <button type="submit"
                        class="w-full btn-brand px-6 py-3 text-white font-semibold rounded-lg transition text-base">
                    Create Account
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">
                    Already have an account?
                    <a href="/login" class="font-medium text-brand hover:underline">Log in</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
