<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found | Kompaza</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 50%, #0ea5e9 100%);
        }
    </style>
</head>
<body class="bg-gray-50 font-[Inter,system-ui,sans-serif] antialiased min-h-screen flex flex-col">

    <!-- Simple nav -->
    <nav class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center h-16">
                <a href="/" class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg hero-gradient flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900">Kompaza</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- 404 Content -->
    <main class="flex-1 flex items-center justify-center px-4 py-20">
        <div class="text-center max-w-md">
            <div class="w-24 h-24 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-8">
                <svg class="w-12 h-12 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <h1 class="text-6xl font-extrabold text-gray-900 mb-4">404</h1>
            <h2 class="text-xl font-semibold text-gray-700 mb-3">Page not found</h2>
            <p class="text-gray-500 mb-8 leading-relaxed">
                Sorry, we could not find the page you are looking for. It might have been moved, deleted, or the URL might be incorrect.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="/" class="w-full sm:w-auto px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition text-sm shadow-sm">
                    Go to Homepage
                </a>
                <a href="javascript:history.back()" class="w-full sm:w-auto px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition text-sm">
                    Go Back
                </a>
            </div>
        </div>
    </main>

    <!-- Simple footer -->
    <footer class="border-t border-gray-100 py-6">
        <div class="text-center text-sm text-gray-400">
            &copy; <?= date('Y') ?> Kompaza. All rights reserved.
        </div>
    </footer>

</body>
</html>
