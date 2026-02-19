<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Kompaza Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md px-6">
        <!-- Logo -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-white">Kompaza</h1>
            <p class="text-gray-400 mt-2">Superadmin Panel</p>
        </div>

        <!-- Flash message -->
        <?php $flash = getFlashMessage(); ?>
        <?php if ($flash): ?>
        <div class="mb-6" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <div class="rounded-lg p-4 <?= $flash['type'] === 'success' ? 'bg-green-900/50 border border-green-700 text-green-300' : 'bg-red-900/50 border border-red-700 text-red-300' ?>">
                <div class="flex items-center justify-between">
                    <span><?= h($flash['message']) ?></span>
                    <button @click="show = false" class="text-current opacity-50 hover:opacity-100">&times;</button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Login form -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-8">
            <h2 class="text-xl font-semibold text-white mb-6">Sign in to your account</h2>

            <form method="POST" action="/login" class="space-y-5">
                <?= csrfField() ?>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email address</label>
                    <input type="email" id="email" name="email" required autofocus
                        class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="admin@kompaza.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Enter your password">
                </div>

                <button type="submit"
                    class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-800">
                    Sign in
                </button>
            </form>
        </div>

        <p class="text-center text-gray-500 text-sm mt-6">Kompaza Platform Administration</p>
    </div>

</body>
</html>
