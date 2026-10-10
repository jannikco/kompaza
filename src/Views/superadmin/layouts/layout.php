<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle ?? 'Dashboard') ?> — Kompaza Admin</title>
    <link rel="icon" href="/images/kompaza-logo.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>
        html { color-scheme: dark; }
        [x-cloak] { display: none !important; }
        /* Dark-theme native controls (date, select, autofill) */
        input, select, textarea { color-scheme: dark; }
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-text-fill-color: #f3f4f6;
            -webkit-box-shadow: 0 0 0px 1000px #374151 inset;
            transition: background-color 5000s ease-in-out 0s;
        }
        /* Keep tables readable when content is wide */
        .sa-table-wrap { -webkit-overflow-scrolling: touch; }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 min-h-full antialiased" x-data="{ mobileMenuOpen: false }">

    <!-- Mobile menu overlay -->
    <div x-show="mobileMenuOpen" x-cloak
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 z-40 lg:hidden"
         @click="mobileMenuOpen = false"></div>

    <!-- Sidebar: closed class includes lg:translate-x-0 so desktop is never hidden -->
    <aside
        :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 border-r border-gray-800 transform transition-transform duration-200 ease-out flex flex-col">
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-5 border-b border-gray-800 shrink-0">
            <a href="/" class="inline-flex items-center bg-white rounded-lg px-3 py-1.5">
                <img src="/images/kompaza-logo.png" alt="Kompaza Admin" class="h-7 w-auto">
            </a>
            <button type="button" @click="mobileMenuOpen = false" class="lg:hidden p-1.5 rounded-md text-gray-400 hover:text-white hover:bg-gray-800" aria-label="Close menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
            <?php
            $navItems = [
                ['href' => '/', 'page' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['href' => '/tenants', 'page' => 'tenants', 'label' => 'Tenants', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ['href' => '/plans', 'page' => 'plans', 'label' => 'Plans', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                ['href' => '/users', 'page' => 'users', 'label' => 'Users', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                ['href' => '/billing/subscriptions', 'page' => 'billing', 'label' => 'Billing', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                ['href' => '/analytics', 'page' => 'analytics', 'label' => 'Analytics', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ['href' => '/audit', 'page' => 'audit', 'label' => 'Audit Log', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['href' => '/system', 'page' => 'system', 'label' => 'System Health', 'icon' => 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01'],
                ['href' => '/settings', 'page' => 'settings', 'label' => 'Settings', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
            ];
            $current = $currentPage ?? '';
            foreach ($navItems as $item):
                $active = $current === $item['page'];
            ?>
            <a href="<?= h($item['href']) ?>"
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors <?= $active ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-900/40' : 'text-gray-300 hover:bg-gray-800 hover:text-white' ?>">
                <svg class="w-5 h-5 mr-3 shrink-0 <?= $active ? 'text-indigo-100' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $item['icon'] ?>"/>
                    <?php if ($item['page'] === 'settings'): ?>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <?php endif; ?>
                </svg>
                <?= h($item['label']) ?>
            </a>
            <?php endforeach; ?>
        </nav>

        <!-- User info at bottom -->
        <div class="p-4 border-t border-gray-800 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-indigo-600/30 border border-indigo-500/40 flex items-center justify-center text-indigo-200 text-sm font-semibold shrink-0">
                    <?= h(mb_strtoupper(mb_substr(currentUser()['name'] ?? 'A', 0, 1))) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate"><?= h(currentUser()['name'] ?? '') ?></p>
                    <p class="text-xs text-gray-400 truncate"><?= h(currentUser()['email'] ?? '') ?></p>
                </div>
                <a href="/logout" class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition" title="Sign out">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main content -->
    <div class="lg:pl-64 min-h-full flex flex-col">
        <!-- Top bar -->
        <header class="sticky top-0 z-30 flex items-center h-16 px-4 sm:px-6 bg-gray-900/90 backdrop-blur border-b border-gray-800 shrink-0">
            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-gray-400 hover:text-white mr-3 p-1.5 rounded-md hover:bg-gray-800" aria-label="Open menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="text-lg font-semibold text-white truncate"><?= h($pageTitle ?? 'Dashboard') ?></h1>
            <div class="ml-auto flex items-center gap-3">
                <span class="hidden sm:inline-flex items-center text-xs font-medium text-indigo-200 bg-indigo-950/80 border border-indigo-800 px-2.5 py-1 rounded-full">Superadmin</span>
            </div>
        </header>

        <!-- Flash message -->
        <?php $flash = getFlashMessage(); ?>
        <?php if ($flash): ?>
        <div class="mx-4 sm:mx-6 mt-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <div class="rounded-lg p-4 <?= $flash['type'] === 'success' ? 'bg-green-950/80 border border-green-800 text-green-300' : 'bg-red-950/80 border border-red-800 text-red-300' ?>">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm"><?= h($flash['message']) ?></span>
                    <button type="button" @click="show = false" class="text-current opacity-50 hover:opacity-100 text-lg leading-none shrink-0" aria-label="Dismiss">&times;</button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Page content -->
        <main class="flex-1 p-4 sm:p-6">
            <?= $content ?? '' ?>
        </main>
    </div>

</body>
</html>
