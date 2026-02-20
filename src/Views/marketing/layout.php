<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle ?? 'Kompaza - All-in-One Content Marketing & Lead Generation Platform') ?></title>
    <?php if (!empty($metaDescription)): ?>
        <meta name="description" content="<?= h($metaDescription) ?>">
    <?php endif; ?>
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= url() ?>">
    <meta property="og:title" content="<?= h($pageTitle ?? 'Kompaza') ?>">
    <meta property="og:description" content="<?= h($metaDescription ?? 'All-in-one platform for content marketing, lead generation, and LinkedIn outreach.') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= url() ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #3b82f6 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-gradient {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 50%, #0ea5e9 100%);
        }
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="bg-white text-gray-900 min-h-screen flex flex-col font-sans antialiased" x-data="{ mobileMenu: false }">

    <!-- Navigation -->
    <nav class="bg-white/95 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-18">
                <!-- Logo -->
                <a href="/" class="flex items-center space-x-2 group">
                    <div class="w-8 h-8 rounded-lg hero-gradient flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900 group-hover:text-indigo-600 transition">Kompaza</span>
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/#features" class="text-gray-600 hover:text-gray-900 transition text-sm font-medium">Features</a>
                    <a href="/pricing" class="text-gray-600 hover:text-gray-900 transition text-sm font-medium">Pricing</a>
                    <a href="/faq" class="text-gray-600 hover:text-gray-900 transition text-sm font-medium">FAQ</a>
                    <a href="/about" class="text-gray-600 hover:text-gray-900 transition text-sm font-medium">About</a>
                    <a href="/login" class="text-gray-600 hover:text-gray-900 transition text-sm font-medium">Log In</a>
                    <a href="/register" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition duration-200 shadow-sm shadow-indigo-600/25 hover:shadow-md hover:shadow-indigo-600/25">
                        Get Started Free
                    </a>
                </div>

                <!-- Mobile menu button -->
                <button @click="mobileMenu = !mobileMenu" class="md:hidden text-gray-600 hover:text-gray-900 p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileMenu" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Mobile Navigation -->
            <div x-show="mobileMenu" x-cloak x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                 class="md:hidden pb-4 space-y-1 border-t border-gray-100 pt-4">
                <a href="/#features" class="block px-3 py-2.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition text-sm font-medium">Features</a>
                <a href="/pricing" class="block px-3 py-2.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition text-sm font-medium">Pricing</a>
                <a href="/faq" class="block px-3 py-2.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition text-sm font-medium">FAQ</a>
                <a href="/about" class="block px-3 py-2.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition text-sm font-medium">About</a>
                <a href="/login" class="block px-3 py-2.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition text-sm font-medium">Log In</a>
                <div class="pt-2">
                    <a href="/register" class="block w-full text-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                        Get Started Free
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <?php
    $flash = getFlashMessage();
    if ($flash): ?>
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             x-transition class="fixed top-20 right-4 z-50 max-w-sm">
            <div class="rounded-lg px-4 py-3 shadow-lg border <?= $flash['type'] === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800' ?>">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium"><?= h($flash['message']) ?></p>
                    <button @click="show = false" class="ml-4 text-current opacity-50 hover:opacity-100">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="flex-1">
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <!-- Brand -->
                <div class="md:col-span-1">
                    <a href="/" class="flex items-center space-x-2 mb-4">
                        <div class="w-8 h-8 rounded-lg hero-gradient flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-white">Kompaza</span>
                    </a>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        The all-in-one platform for content marketing, lead generation, and LinkedIn automation.
                    </p>
                </div>

                <!-- Product -->
                <div>
                    <h3 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Product</h3>
                    <ul class="space-y-3 text-sm">
                        <li><a href="/#features" class="text-gray-400 hover:text-white transition">Features</a></li>
                        <li><a href="/pricing" class="text-gray-400 hover:text-white transition">Pricing</a></li>
                        <li><a href="/faq" class="text-gray-400 hover:text-white transition">FAQ</a></li>
                        <li><a href="/register" class="text-gray-400 hover:text-white transition">Get Started</a></li>
                    </ul>
                </div>

                <!-- Company -->
                <div>
                    <h3 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Company</h3>
                    <ul class="space-y-3 text-sm">
                        <li><a href="/about" class="text-gray-400 hover:text-white transition">About Us</a></li>
                        <li><a href="/login" class="text-gray-400 hover:text-white transition">Log In</a></li>
                        <li><a href="mailto:support@kompaza.com" class="text-gray-400 hover:text-white transition">Support</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h3 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Legal</h3>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Terms of Service</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row items-center justify-between">
                <p class="text-gray-500 text-sm">&copy; <?= date('Y') ?> Kompaza. All rights reserved.</p>
                <div class="flex items-center space-x-6 mt-4 md:mt-0">
                    <a href="#" class="text-gray-500 hover:text-gray-300 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-gray-300 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
