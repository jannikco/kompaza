<?php
$tenant = $tenant ?? currentTenant();
$primaryColor = $tenant['primary_color'] ?? '#3b82f6';
$companyName = $tenant['company_name'] ?? $tenant['name'] ?? 'Store';
$logoUrl = imageUrl($tenant['logo_url'] ?? '') ?: null;
$faviconUrl = imageUrl($tenant['favicon_url'] ?? '') ?: null;
$customCss = $tenant['custom_css'] ?? null;
$tenantId = $tenant['id'] ?? null;
$currency = $tenant['currency'] ?? 'DKK';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle ?? 'Home') ?> — <?= h($companyName) ?></title>
    <?php if (!empty($metaDescription)): ?>
        <meta name="description" content="<?= h($metaDescription) ?>">
    <?php endif; ?>
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= url() ?>">
    <meta property="og:title" content="<?= h($pageTitle ?? $companyName) ?>">
    <meta property="og:description" content="<?= h($metaDescription ?? '') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= url() ?>">
    <?php if ($faviconUrl): ?>
        <link rel="icon" href="<?= h($faviconUrl) ?>" type="image/x-icon">
    <?php endif; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: '<?= h($primaryColor) ?>',
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        .btn-brand {
            background-color: <?= h($primaryColor) ?>;
        }
        .btn-brand:hover {
            filter: brightness(0.9);
        }
        .text-brand {
            color: <?= h($primaryColor) ?>;
        }
        .border-brand {
            border-color: <?= h($primaryColor) ?>;
        }
        .bg-brand {
            background-color: <?= h($primaryColor) ?>;
        }
        .ring-brand {
            --tw-ring-color: <?= h($primaryColor) ?>;
        }
    </style>
    <?php if ($customCss): ?>
        <style><?= $customCss ?></style>
    <?php endif; ?>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col font-sans antialiased" x-data="{ mobileMenu: false }">

    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo / Company Name -->
                <a href="/" class="flex items-center space-x-3 group flex-shrink-0">
                    <?php if ($logoUrl): ?>
                        <img src="<?= h($logoUrl) ?>" alt="<?= h($companyName) ?>" class="h-8 w-auto">
                    <?php else: ?>
                        <span class="text-xl font-bold text-gray-900 group-hover:text-brand transition"><?= h($companyName) ?></span>
                    <?php endif; ?>
                </a>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center space-x-8">
                    <?php if (tenantFeature('blog')): ?>
                        <a href="/blog" class="text-gray-600 hover:text-gray-900 transition text-sm font-medium">Blog</a>
                    <?php endif; ?>
                    <?php if (tenantFeature('ebooks')): ?>
                        <a href="/ebooks" class="text-gray-600 hover:text-gray-900 transition text-sm font-medium">Ebooks</a>
                    <?php endif; ?>
                    <?php if (tenantFeature('courses')): ?>
                        <a href="/courses" class="text-gray-600 hover:text-gray-900 transition text-sm font-medium">Courses</a>
                    <?php endif; ?>
                    <?php if (tenantFeature('orders')): ?>
                        <a href="/produkter" class="text-gray-600 hover:text-gray-900 transition text-sm font-medium">Products</a>
                        <a href="/kurv" class="relative text-gray-600 hover:text-gray-900 transition text-sm font-medium"
                           x-data="{ count: 0 }" x-init="
                                let cart = JSON.parse(localStorage.getItem('kz_cart_<?= (int)$tenantId ?>') || '[]');
                                count = cart.reduce((s, i) => s + i.qty, 0);
                                window.addEventListener('cart-updated', () => {
                                    let c = JSON.parse(localStorage.getItem('kz_cart_<?= (int)$tenantId ?>') || '[]');
                                    count = c.reduce((s, i) => s + i.qty, 0);
                                });
                           ">
                            <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                            <span x-show="count > 0" x-text="count" class="absolute -top-2 -right-3 bg-brand text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold" x-cloak></span>
                        </a>
                    <?php endif; ?>
                </nav>

                <!-- Right side: Account -->
                <div class="hidden md:flex items-center space-x-4">
                    <?php if (isAuthenticated() && isCustomer()): ?>
                        <a href="/konto" class="text-gray-600 hover:text-gray-900 transition text-sm font-medium">My Account</a>
                        <a href="/logout" class="text-gray-500 hover:text-gray-700 transition text-sm">Log Out</a>
                    <?php else: ?>
                        <a href="/login" class="text-gray-600 hover:text-gray-900 transition text-sm font-medium">Log In</a>
                    <?php endif; ?>
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
                <?php if (tenantFeature('blog')): ?>
                    <a href="/blog" class="block px-3 py-2.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition text-sm font-medium">Blog</a>
                <?php endif; ?>
                <?php if (tenantFeature('ebooks')): ?>
                    <a href="/ebooks" class="block px-3 py-2.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition text-sm font-medium">Ebooks</a>
                <?php endif; ?>
                <?php if (tenantFeature('courses')): ?>
                    <a href="/courses" class="block px-3 py-2.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition text-sm font-medium">Courses</a>
                <?php endif; ?>
                <?php if (tenantFeature('orders')): ?>
                    <a href="/produkter" class="block px-3 py-2.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition text-sm font-medium">Products</a>
                    <a href="/kurv" class="block px-3 py-2.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition text-sm font-medium">Cart</a>
                <?php endif; ?>
                <div class="border-t border-gray-100 pt-2 mt-2">
                    <?php if (isAuthenticated() && isCustomer()): ?>
                        <a href="/konto" class="block px-3 py-2.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition text-sm font-medium">My Account</a>
                        <a href="/logout" class="block px-3 py-2.5 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-50 transition text-sm">Log Out</a>
                    <?php else: ?>
                        <a href="/login" class="block px-3 py-2.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition text-sm font-medium">Log In</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Flash messages -->
    <?php $flash = getFlashMessage(); ?>
    <?php if ($flash): ?>
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
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Brand -->
                <div>
                    <?php if ($logoUrl): ?>
                        <img src="<?= h($logoUrl) ?>" alt="<?= h($companyName) ?>" class="h-8 w-auto mb-4">
                    <?php else: ?>
                        <p class="text-lg font-bold text-gray-900 mb-4"><?= h($companyName) ?></p>
                    <?php endif; ?>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        <?= h($tenant['tagline'] ?? '') ?>
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-gray-900 font-semibold mb-4 text-sm uppercase tracking-wider">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <?php if (tenantFeature('blog')): ?>
                            <li><a href="/blog" class="text-gray-500 hover:text-gray-700 transition">Blog</a></li>
                        <?php endif; ?>
                        <?php if (tenantFeature('ebooks')): ?>
                            <li><a href="/ebooks" class="text-gray-500 hover:text-gray-700 transition">Ebooks</a></li>
                        <?php endif; ?>
                        <?php if (tenantFeature('courses')): ?>
                            <li><a href="/courses" class="text-gray-500 hover:text-gray-700 transition">Courses</a></li>
                        <?php endif; ?>
                        <?php if (tenantFeature('orders')): ?>
                            <li><a href="/produkter" class="text-gray-500 hover:text-gray-700 transition">Products</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Legal & Support -->
                <div>
                    <h3 class="text-gray-900 font-semibold mb-4 text-sm uppercase tracking-wider">Support</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/contact" class="text-gray-500 hover:text-gray-700 transition">Contact Us</a></li>
                        <li><a href="/privatlivspolitik" class="text-gray-500 hover:text-gray-700 transition">Privacy Policy</a></li>
                        <li><a href="/terms" class="text-gray-500 hover:text-gray-700 transition">Terms of Service</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-200 mt-8 pt-8 text-center">
                <p class="text-gray-400 text-sm">&copy; <?= date('Y') ?> <?= h($companyName) ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>
