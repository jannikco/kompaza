<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle ?? 'Admin') ?> — <?= h($tenant['company_name'] ?? $tenant['name'] ?? 'Kompaza') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen" x-data="{ sidebarOpen: true, mobileMenuOpen: false }">

    <!-- Mobile menu overlay -->
    <div x-show="mobileMenuOpen" x-cloak class="fixed inset-0 bg-black/50 z-40 lg:hidden" @click="mobileMenuOpen = false"></div>

    <!-- Sidebar -->
    <aside :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-800 border-r border-gray-700 transform transition-transform lg:translate-x-0 flex flex-col">
        <!-- Logo -->
        <div class="flex items-center h-16 px-6 border-b border-gray-700">
            <a href="/admin" class="text-xl font-bold text-white"><?= h($tenant['company_name'] ?? $tenant['name'] ?? 'Dashboard') ?></a>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="/admin" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= ($currentPage ?? '') === 'dashboard' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            <?php if (tenantFeature('lead_magnets')): ?>
            <a href="/admin/lead-magnets" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= ($currentPage ?? '') === 'lead-magnets' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Lead Magnets
            </a>
            <?php endif; ?>

            <?php if (tenantFeature('blog')): ?>
            <a href="/admin/artikler" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= ($currentPage ?? '') === 'articles' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                Articles
            </a>
            <?php endif; ?>

            <?php if (tenantFeature('ebooks')): ?>
            <a href="/admin/eboger" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= ($currentPage ?? '') === 'ebooks' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Ebooks
            </a>
            <?php endif; ?>

            <?php if (tenantFeature('courses')): ?>
            <a href="/admin/kurser" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= ($currentPage ?? '') === 'courses' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                Courses
            </a>
            <?php endif; ?>

            <?php if (tenantFeature('orders')): ?>
            <div class="pt-4 pb-2 px-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Commerce</p>
            </div>
            <a href="/admin/produkter" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= ($currentPage ?? '') === 'products' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Products
            </a>
            <a href="/admin/ordrer" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= ($currentPage ?? '') === 'orders' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Orders
            </a>
            <?php endif; ?>

            <a href="/admin/kunder" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= ($currentPage ?? '') === 'customers' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Customers
            </a>

            <?php if (tenantFeature('connectpilot')): ?>
            <div class="pt-4 pb-2 px-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">ConnectPilot</p>
            </div>
            <a href="/admin/connectpilot" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= ($currentPage ?? '') === 'connectpilot' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                ConnectPilot
            </a>
            <a href="/admin/connectpilot/kampagner" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= ($currentPage ?? '') === 'connectpilot-campaigns' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/></svg>
                Campaigns
            </a>
            <a href="/admin/connectpilot/leads" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= ($currentPage ?? '') === 'connectpilot-leads' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Leads
            </a>
            <?php endif; ?>

            <div class="pt-4 pb-2 px-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Payments</p>
            </div>
            <a href="/admin/abonnement" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= ($currentPage ?? '') === 'subscription' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Subscription
            </a>
            <a href="/admin/stripe-connect" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= ($currentPage ?? '') === 'stripe-connect' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                Stripe Connect
            </a>
            <a href="/admin/salg" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= ($currentPage ?? '') === 'sales' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Sales
            </a>

            <div class="pt-4 pb-2 px-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">System</p>
            </div>
            <a href="/admin/tilmeldinger" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= ($currentPage ?? '') === 'signups' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Email Signups
            </a>
            <a href="/admin/brugere" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= ($currentPage ?? '') === 'users' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Users
            </a>
            <a href="/admin/indstillinger" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg <?= ($currentPage ?? '') === 'settings' ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Settings
            </a>
        </nav>

        <!-- User info at bottom -->
        <div class="p-4 border-t border-gray-700">
            <div class="flex items-center">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate"><?= h(currentUser()['name'] ?? '') ?></p>
                    <p class="text-xs text-gray-400 truncate"><?= h(currentUser()['email'] ?? '') ?></p>
                </div>
                <a href="/logout" class="ml-3 text-gray-400 hover:text-white" title="Sign out">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main content -->
    <div class="lg:ml-64">
        <!-- Top bar -->
        <header class="sticky top-0 z-30 flex items-center h-16 px-6 bg-gray-800 border-b border-gray-700">
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-gray-400 hover:text-white mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="text-lg font-semibold text-white"><?= h($pageTitle ?? 'Dashboard') ?></h1>
            <div class="ml-auto flex items-center space-x-4">
                <a href="/" target="_blank" class="text-sm text-gray-400 hover:text-white">View Site</a>
            </div>
        </header>

        <!-- Impersonation banner -->
        <?php if (isset($_COOKIE['impersonating'])): ?>
        <div class="bg-yellow-600 text-white px-4 py-2 text-sm flex items-center justify-between">
            <span>You are logged in as <?= h(\App\Auth\Auth::admin()['name'] ?? 'tenant admin') ?> (support mode)</span>
            <a href="https://superadmin.<?= h(PLATFORM_DOMAIN) ?>/tenants" class="underline font-medium">Back to Superadmin</a>
        </div>
        <?php endif; ?>

        <!-- Flash message -->
        <?php $flash = getFlashMessage(); ?>
        <?php if ($flash): ?>
        <div class="mx-6 mt-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <div class="rounded-lg p-4 <?= $flash['type'] === 'success' ? 'bg-green-900/50 border border-green-700 text-green-300' : 'bg-red-900/50 border border-red-700 text-red-300' ?>">
                <div class="flex items-center justify-between">
                    <span><?= h($flash['message']) ?></span>
                    <button @click="show = false" class="text-current opacity-50 hover:opacity-100">&times;</button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Page content -->
        <main class="p-6">
            <?= $content ?? '' ?>
        </main>
    </div>

</body>
</html>
