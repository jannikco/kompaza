<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Kompaza Admin</title>
    <link rel="icon" href="/images/kompaza-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>
        html { color-scheme: dark; }
        [x-cloak] { display: none !important; }
        input { color-scheme: dark; }
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-text-fill-color: #f3f4f6;
            -webkit-box-shadow: 0 0 0px 1000px #374151 inset;
            transition: background-color 5000s ease-in-out 0s;
        }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 min-h-full flex items-center justify-center antialiased px-4">

    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center bg-white rounded-xl px-5 py-3 mb-4 shadow-lg shadow-black/30">
                <img src="/images/kompaza-logo.png" alt="Kompaza" class="h-11 w-auto">
            </div>
            <h1 class="text-xl font-semibold text-white tracking-tight">Superadmin Panel</h1>
            <p class="text-sm text-gray-400 mt-1">Platform administration for Kompaza</p>
        </div>

        <!-- Flash message -->
        <?php $flash = getFlashMessage(); ?>
        <?php if ($flash): ?>
        <div class="mb-6" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <div class="rounded-lg p-4 <?= $flash['type'] === 'success' ? 'bg-green-950/80 border border-green-800 text-green-300' : 'bg-red-950/80 border border-red-800 text-red-300' ?>">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm"><?= h($flash['message']) ?></span>
                    <button type="button" @click="show = false" class="text-current opacity-50 hover:opacity-100" aria-label="Dismiss">&times;</button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Login form -->
        <div class="bg-gray-900 rounded-2xl border border-gray-800 p-8 shadow-xl shadow-black/40">
            <h2 class="text-lg font-semibold text-white mb-6">Sign in to your account</h2>

            <form method="POST" action="/login" class="space-y-5">
                <?= csrfField() ?>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">Email address</label>
                    <input type="email" id="email" name="email" required autofocus autocomplete="username"
                        class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="jh@profectify.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password"
                        class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Enter your password">
                </div>

                <button type="submit"
                    class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-lg transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-900">
                    Sign in
                </button>
            </form>
        </div>

        <p class="text-center text-gray-500 text-sm mt-8">Session lasts 180 days · Kompaza Platform</p>
    </div>

</body>
</html>
