#!/usr/bin/env php
<?php
/**
 * AIbootcamp Custom Pages Migration
 *
 * Converts aibootcamp marketing pages to static HTML and inserts them
 * as custom_pages for the aibootcamphq tenant in Kompaza.
 *
 * Usage: php scripts/migrate-aibootcamp-pages.php [--dry-run]
 */

$dryRun = in_array('--dry-run', $argv);

// Database config - UPDATE THESE ON SERVER
$kompazaDb = new PDO('mysql:host=127.0.0.1;dbname=kompaza;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// Get tenant ID
$stmt = $kompazaDb->prepare("SELECT id FROM tenants WHERE slug = 'aibootcamphq'");
$stmt->execute();
$tenant = $stmt->fetch();
if (!$tenant) {
    die("ERROR: Tenant 'aibootcamphq' not found.\n");
}
$tenantId = $tenant['id'];
echo "Tenant ID: $tenantId\n";

if ($dryRun) {
    echo "=== DRY RUN MODE ===\n\n";
}

// Link mapping for internal routes
$linkMap = [
    'href="/free-ai-prompts"' => 'href="/lp/free-ai-prompts"',
    'href="/free-atlas"' => 'href="/lp/free-atlas"',
    'href="/free-udlaeg"' => 'href="/lp/free-udlaeg"',
    'href="/free-konsulent-ai-tools"' => 'href="/lp/free-ai-tools"',
    'href="/eboeger"' => 'href="/eboger"',
    'href="/privacy-policy"' => 'href="/privacy"',
    'href="/terms-of-service"' => 'href="/terms"',
    'href="https://aibootcamp.dk/book-konsulent"' => 'href="/consultation"',
    'href="/book-konsulent"' => 'href="/consultation"',
    'href="/course-purchase' => 'href="/courses',
    'href="/#courses"' => 'href="/courses"',
    '&copy; 2025 AI BootCamp' => '&copy; 2026 AI BootCamp HQ',
    '&copy; 2024 AI BootCamp' => '&copy; 2026 AI BootCamp HQ',
];

function updateLinks($html, $linkMap) {
    foreach ($linkMap as $old => $new) {
        $html = str_replace($old, $new, $html);
    }
    // Remove Matomo tracking
    $html = preg_replace('/<!-- Matomo -->.*?<!-- End Matomo Code -->/s', '', $html);
    // Also remove standalone Matomo blocks (without end comment)
    $html = preg_replace('/<!-- Matomo -->\s*<script>.*?<\/script>/s', '', $html);
    // Remove Brevo chat widget
    $html = preg_replace('/<!-- Brevo Conversations.*?<\/script>/s', '', $html);
    return $html;
}

// =============================
// PAGE 1: Gratis (Free Resources)
// =============================
echo "Building 'gratis' page...\n";

$gratisHtml = <<<'GRATIS_HTML'
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gratis PDF'er & AI Guides - AI BootCamp HQ</title>
    <meta name="description" content="Download gratis AI guides, prompts og ressourcer. Alt fra 300 AI prompts til Atlas browser guide og mere - helt gratis.">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .hero-pattern {
            background-image: radial-gradient(circle at 25% 25%, #f0fdf4 0%, transparent 50%),
                            radial-gradient(circle at 75% 75%, #dcfce7 0%, transparent 50%);
        }
        .text-gradient-green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .animate-float { animation: float 5s ease-in-out infinite; }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fdf4', 100: '#dcfce7', 200: '#bbf7d0', 300: '#86efac',
                            400: '#4ade80', 500: '#10b981', 600: '#059669', 700: '#047857',
                            800: '#065f46', 900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gray-50">
    <nav class="bg-white shadow-md fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center">
                    <a href="/" class="flex items-center">
                        <span class="text-2xl font-bold">
                            <span class="text-gradient-green">AI</span> BootCamp
                        </span>
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/" class="text-gray-700 hover:text-primary-600">Hjem</a>
                    <a href="/courses" class="text-gray-700 hover:text-primary-600">Kurser</a>
                    <a href="/eboger" class="text-gray-700 hover:text-primary-600">Ebøger</a>
                    <a href="/gratis" class="text-primary-600 font-semibold">Gratis PDF'er</a>
                    <a href="/login" class="bg-gradient-to-r from-primary-600 to-green-600 text-white px-6 py-2 rounded-lg hover:opacity-90 transition">Log ind</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="pt-24 pb-4 bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center space-x-2 text-sm">
                <a href="/" class="text-gray-500 hover:text-primary-600">Hjem</a>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="text-primary-600 font-semibold">Gratis PDF'er</span>
            </nav>
        </div>
    </div>

    <section class="pt-16 pb-20 hero-pattern">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="animate-float inline-block mb-6">
                <svg class="w-20 h-20 text-primary-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6">Gratis <span class="text-gradient-green">AI Ressourcer</span></h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto mb-8">Download praktiske AI guides, prompt-samlinger og værktøjer helt gratis. Alt hvad du behøver for at komme i gang med AI - ingen kreditkort påkrævet.</p>
            <div class="flex items-center justify-center gap-6">
                <div class="flex items-center"><svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg><span class="ml-1 text-gray-600">100% Gratis</span></div>
                <div class="flex items-center"><svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="ml-1 text-gray-600">Øjeblikkelig adgang</span></div>
                <div class="flex items-center"><svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg><span class="ml-1 text-gray-600">Sendes til din email</span></div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="card-hover bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-8 border-2 border-blue-100">
                    <div class="flex flex-col h-full">
                        <div class="mb-6">
                            <div class="w-16 h-16 bg-blue-100 rounded-xl flex items-center justify-center mb-6"><span class="text-4xl">💬</span></div>
                            <span class="inline-block bg-blue-600 text-white px-4 py-1 rounded-full text-sm font-semibold mb-4">GRATIS</span>
                            <h2 class="text-3xl font-bold text-gray-900 mb-3">300 AI Prompts</h2>
                            <p class="text-lg text-gray-600 mb-4">Professionelle prompts til IT, Salg, Marketing, HR, Ledelse og mere</p>
                        </div>
                        <div class="flex-1 space-y-3 mb-6">
                            <div class="flex items-start space-x-2"><svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span class="text-gray-700">300 færdige prompts</span></div>
                            <div class="flex items-start space-x-2"><svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span class="text-gray-700">10 forskellige brancher</span></div>
                            <div class="flex items-start space-x-2"><svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span class="text-gray-700">Klar til ChatGPT & Claude</span></div>
                        </div>
                        <div class="mt-auto"><a href="/lp/free-ai-prompts" class="block w-full text-center bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4 rounded-lg font-semibold hover:opacity-90 transition transform hover:scale-105">Download gratis →</a></div>
                    </div>
                </div>

                <div class="card-hover bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-8 border-2 border-amber-100">
                    <div class="flex flex-col h-full">
                        <div class="mb-6">
                            <div class="w-16 h-16 bg-amber-100 rounded-xl flex items-center justify-center mb-6"><span class="text-4xl">🗺️</span></div>
                            <span class="inline-block bg-amber-600 text-white px-4 py-1 rounded-full text-sm font-semibold mb-4">GRATIS</span>
                            <h2 class="text-3xl font-bold text-gray-900 mb-3">Atlas Browser Guide</h2>
                            <p class="text-lg text-gray-600 mb-4">Lær at bruge Atlas browser professionelt med AI-assisteret browsing</p>
                        </div>
                        <div class="flex-1 space-y-3 mb-6">
                            <div class="flex items-start space-x-2"><svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span class="text-gray-700">Atlas browser introduktion</span></div>
                            <div class="flex items-start space-x-2"><svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span class="text-gray-700">Praktiske prompts</span></div>
                            <div class="flex items-start space-x-2"><svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span class="text-gray-700">Kilder & citater guide</span></div>
                        </div>
                        <div class="mt-auto"><a href="/lp/free-atlas" class="block w-full text-center bg-gradient-to-r from-amber-600 to-orange-600 text-white px-6 py-4 rounded-lg font-semibold hover:opacity-90 transition transform hover:scale-105">Download gratis →</a></div>
                    </div>
                </div>

                <div class="card-hover bg-gradient-to-br from-red-50 to-rose-50 rounded-2xl p-8 border-2 border-red-100">
                    <div class="flex flex-col h-full">
                        <div class="mb-6">
                            <div class="w-16 h-16 bg-red-100 rounded-xl flex items-center justify-center mb-6"><span class="text-4xl">⚠️</span></div>
                            <span class="inline-block bg-red-600 text-white px-4 py-1 rounded-full text-sm font-semibold mb-4">GRATIS</span>
                            <h2 class="text-3xl font-bold text-gray-900 mb-3">AI-Kvitteringer</h2>
                            <p class="text-lg text-gray-600 mb-4">Beskyt din virksomhed mod AI-genererede kvitteringer i udlægssystemet</p>
                        </div>
                        <div class="flex-1 space-y-3 mb-6">
                            <div class="flex items-start space-x-2"><svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span class="text-gray-700">Identificer falske kvitteringer</span></div>
                            <div class="flex items-start space-x-2"><svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span class="text-gray-700">Forebyg svindel</span></div>
                            <div class="flex items-start space-x-2"><svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span class="text-gray-700">Praktiske tjeklister</span></div>
                        </div>
                        <div class="mt-auto"><a href="/lp/free-udlaeg" class="block w-full text-center bg-gradient-to-r from-red-600 to-rose-600 text-white px-6 py-4 rounded-lg font-semibold hover:opacity-90 transition transform hover:scale-105">Download gratis →</a></div>
                    </div>
                </div>

                <div class="card-hover bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl p-8 border-2 border-purple-100">
                    <div class="flex flex-col h-full">
                        <div class="mb-6">
                            <div class="w-16 h-16 bg-purple-100 rounded-xl flex items-center justify-center mb-6"><span class="text-4xl">🛠️</span></div>
                            <span class="inline-block bg-purple-600 text-white px-4 py-1 rounded-full text-sm font-semibold mb-4">GRATIS</span>
                            <h2 class="text-3xl font-bold text-gray-900 mb-3">10 AI-Værktøjer</h2>
                            <p class="text-lg text-gray-600 mb-4">Den praktiske guide til hvordan danske konsulenter arbejder hurtigere med AI</p>
                        </div>
                        <div class="flex-1 space-y-3 mb-6">
                            <div class="flex items-start space-x-2"><svg class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span class="text-gray-700">10 essentielle AI-værktøjer</span></div>
                            <div class="flex items-start space-x-2"><svg class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span class="text-gray-700">Konkrete use cases & prompts</span></div>
                            <div class="flex items-start space-x-2"><svg class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span class="text-gray-700">Fra værktøjer til automation-pakker</span></div>
                        </div>
                        <div class="mt-auto"><a href="/lp/free-ai-tools" class="block w-full text-center bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-4 rounded-lg font-semibold hover:opacity-90 transition transform hover:scale-105">Download gratis →</a></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Hvorfor downloade vores gratis ressourcer?</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Vi deler vores viden og erfaring for at hjælpe dig med at komme i gang med AI</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4"><svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Kvalitet</h3>
                    <p class="text-gray-600">Professionelt indhold baseret på virkelige cases</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4"><svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg></div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Praktisk</h3>
                    <p class="text-gray-600">Klar til brug - ingen teori, kun praksis</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4"><svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Hurtigt</h3>
                    <p class="text-gray-600">Øjeblikkelig levering til din email</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4"><svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg></div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Opdateret</h3>
                    <p class="text-gray-600">Løbende opdateret med nye AI-trends</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-gradient-to-r from-primary-600 to-green-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold text-white mb-6">Klar til at komme i gang med AI?</h2>
            <p class="text-xl text-white/90 mb-8 max-w-2xl mx-auto">Download alle vores gratis ressourcer og få adgang til ugentlige AI-tips direkte i din indbakke.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/lp/free-ai-prompts" class="inline-block bg-white text-primary-600 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 transition transform hover:scale-105">300 AI Prompts →</a>
                <a href="/lp/free-atlas" class="inline-block bg-white/10 backdrop-blur text-white border-2 border-white px-8 py-4 rounded-lg font-semibold hover:bg-white/20 transition">Atlas Guide →</a>
            </div>
        </div>
    </section>

    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div><h3 class="text-xl font-bold mb-4">AI BootCamp HQ</h3><p class="text-gray-400">Din trusted partner til AI-uddannelse og -værktøjer.</p></div>
                <div><h4 class="font-semibold mb-4">Produkter</h4><ul class="space-y-2 text-gray-400"><li><a href="/courses" class="hover:text-white transition">Online Kurser</a></li><li><a href="/eboger" class="hover:text-white transition">Ebøger</a></li><li><a href="/gratis" class="hover:text-white transition">Gratis PDF'er</a></li></ul></div>
                <div><h4 class="font-semibold mb-4">Support</h4><ul class="space-y-2 text-gray-400"><li><a href="/contact" class="hover:text-white transition">Kontakt</a></li></ul></div>
                <div><h4 class="font-semibold mb-4">Juridisk</h4><ul class="space-y-2 text-gray-400"><li><a href="/privacy" class="hover:text-white transition">Privatlivspolitik</a></li><li><a href="/terms" class="hover:text-white transition">Handelsbetingelser</a></li></ul></div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400"><p>&copy; 2026 AI BootCamp HQ. Alle rettigheder forbeholdes.</p></div>
        </div>
    </footer>
</body>
</html>
GRATIS_HTML;

// =============================
// PAGE 2: Foredrag (Speaking/Lectures)
// =============================
echo "Building 'foredrag' page...\n";

// Read original foredrag source and strip PHP
$foredragSource = file_get_contents('/var/www/html/kursus.aibootcamp.dk/src/Views/foredrag.php');
if (!$foredragSource) {
    echo "WARNING: Could not read foredrag.php source - creating from embedded content\n";
    $foredragSource = '';
}

// If we have the source, process it; otherwise use embedded version
if ($foredragSource) {
    // Remove PHP session conditional nav - keep only the public nav (the else block)
    // Remove the logged-in nav block
    $foredragHtml = preg_replace(
        '/\s*<\?php if \(isset\(\$_SESSION\[\'user_id\'\]\)\): \?>\s*<nav.*?<\/nav>\s*<\?php else: \?>/s',
        '',
        $foredragSource
    );
    // Remove the endif
    $foredragHtml = preg_replace('/\s*<\?php endif; \?>/s', '', $foredragHtml);
    // Replace dynamic year
    $foredragHtml = str_replace('<?= date(\'Y\') ?>', '2026', $foredragHtml);
    // Remove any remaining PHP tags
    $foredragHtml = preg_replace('/<\?php.*?\?>/s', '', $foredragHtml);
    $foredragHtml = preg_replace('/<\?=.*?\?>/s', '', $foredragHtml);
    // Update links
    $foredragHtml = updateLinks($foredragHtml, $linkMap);
    // Fix title
    $foredragHtml = str_replace('AI BootCamp</title>', 'AI BootCamp HQ</title>', $foredragHtml);
    // Update book consultation link that uses target="_blank"
    $foredragHtml = str_replace('target="_blank"', '', $foredragHtml);
} else {
    $foredragHtml = '<h1>Foredrag page - content pending</h1>';
}

// =============================
// PAGE 3: AI Konsulent (Mastermind Program)
// =============================
echo "Building 'ai-konsulent' page...\n";

$aiKonsulentSource = file_get_contents('/var/www/html/kursus.aibootcamp.dk/src/Views/ai-konsulent.php');
if (!$aiKonsulentSource) {
    echo "WARNING: Could not read ai-konsulent.php source\n";
    $aiKonsulentSource = '';
}

if ($aiKonsulentSource) {
    // Remove Stripe.js include
    $aiKonsulentHtml = str_replace('<script src="https://js.stripe.com/v3/"></script>', '', $aiKonsulentSource);

    // Replace the entire x-data Alpine block with a simple version (no Stripe)
    // Find the body x-data attribute and simplify it
    $aiKonsulentHtml = preg_replace(
        '/<body class="bg-white" x-data="\{.*?\}">/s',
        '<body class="bg-white" x-data="{ showPayment: false, selectedTier: \'early_bird\' }">',
        $aiKonsulentHtml
    );

    // Replace payment buttons with contact links
    $aiKonsulentHtml = str_replace(
        '@click="selectTier(\'founders\')"',
        'onclick="window.location.href=\'/consultation\'"'
    , $aiKonsulentHtml);
    $aiKonsulentHtml = str_replace(
        '@click="selectTier(\'early_bird\')"',
        'onclick="window.location.href=\'/consultation\'"'
    , $aiKonsulentHtml);
    $aiKonsulentHtml = str_replace(
        '@click="selectTier(\'standard\')"',
        'onclick="window.location.href=\'/consultation\'"'
    , $aiKonsulentHtml);

    // Replace final CTA button
    $aiKonsulentHtml = str_replace(
        "@click=\"\$el.closest('body').querySelector('#pricing').scrollIntoView({ behavior: 'smooth' })\"",
        'onclick="window.location.href=\'/consultation\'"'
    , $aiKonsulentHtml);

    // Remove the entire payment form section (x-show="showPayment")
    $aiKonsulentHtml = preg_replace(
        '/<!-- Enrollment Form with Stripe Payment -->.*?<\/section>/s',
        '',
        $aiKonsulentHtml
    );

    // Remove Stripe Element styles
    $aiKonsulentHtml = preg_replace('/\.StripeElement\s*\{.*?\}/s', '', $aiKonsulentHtml);
    $aiKonsulentHtml = preg_replace('/\.StripeElement--focus\s*\{.*?\}/s', '', $aiKonsulentHtml);
    $aiKonsulentHtml = preg_replace('/\.StripeElement--invalid\s*\{.*?\}/s', '', $aiKonsulentHtml);

    // Remove any PHP tags
    $aiKonsulentHtml = preg_replace('/<\?=.*?\?>/s', '', $aiKonsulentHtml);
    $aiKonsulentHtml = preg_replace('/<\?php.*?\?>/s', '', $aiKonsulentHtml);

    // Update links
    $aiKonsulentHtml = updateLinks($aiKonsulentHtml, $linkMap);

    // Fix title
    $aiKonsulentHtml = str_replace('AI BootCamp</title>', 'AI BootCamp HQ</title>', $aiKonsulentHtml);
} else {
    $aiKonsulentHtml = '<h1>AI Konsulent page - content pending</h1>';
}

// =============================
// PAGE 4: Hjemmeside (Homepage - Course Landing Page)
// =============================
echo "Building homepage...\n";

// Read the source and replace the PHP module loop with hardcoded HTML
$hjemmesideSource = file_get_contents('/var/www/html/kursus.aibootcamp.dk/src/Views/hjemmeside.php');
if (!$hjemmesideSource) {
    echo "WARNING: Could not read hjemmeside.php source\n";
    $hjemmesideSource = '';
}

if ($hjemmesideSource) {
    // Replace the PHP foreach module loop with hardcoded module cards
    $moduleCardsHtml = '
                <div class="module-card bg-gray-50 p-6 rounded-xl hover:bg-white hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start">
                        <div class="text-3xl mr-4">🚀</div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-bold text-gray-900">Modul 1: Intro &amp; mindset</h3>
                                <span class="text-sm text-gray-500">10 min</span>
                            </div>
                            <p class="text-gray-600">Hvorfor AI + Replit = et gennembrud for non-codere</p>
                        </div>
                    </div>
                </div>
                <div class="module-card bg-gray-50 p-6 rounded-xl hover:bg-white hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start">
                        <div class="text-3xl mr-4">⚡</div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-bold text-gray-900">Modul 2: Kom i gang med Replit</h3>
                                <span class="text-sm text-gray-500">20 min</span>
                            </div>
                            <p class="text-gray-600">Opret konto og lær Replit-dashboard at kende</p>
                        </div>
                    </div>
                </div>
                <div class="module-card bg-gray-50 p-6 rounded-xl hover:bg-white hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start">
                        <div class="text-3xl mr-4">🏗️</div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-bold text-gray-900">Modul 3: Byg grundstrukturen</h3>
                                <span class="text-sm text-gray-500">30 min</span>
                            </div>
                            <p class="text-gray-600">Brug AI til at generere HTML/CSS/JS-struktur</p>
                        </div>
                    </div>
                </div>
                <div class="module-card bg-gray-50 p-6 rounded-xl hover:bg-white hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start">
                        <div class="text-3xl mr-4">🎨</div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-bold text-gray-900">Modul 4: Design &amp; branding</h3>
                                <span class="text-sm text-gray-500">25 min</span>
                            </div>
                            <p class="text-gray-600">Tilpas farver, fonts og layout med AI prompts</p>
                        </div>
                    </div>
                </div>
                <div class="module-card bg-gray-50 p-6 rounded-xl hover:bg-white hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start">
                        <div class="text-3xl mr-4">✍️</div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-bold text-gray-900">Modul 5: Tekster &amp; indhold</h3>
                                <span class="text-sm text-gray-500">25 min</span>
                            </div>
                            <p class="text-gray-600">AI-genererede tekster og SEO basics</p>
                        </div>
                    </div>
                </div>
                <div class="module-card bg-gray-50 p-6 rounded-xl hover:bg-white hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start">
                        <div class="text-3xl mr-4">🌐</div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-bold text-gray-900">Modul 6: Publicering</h3>
                                <span class="text-sm text-gray-500">20 min</span>
                            </div>
                            <p class="text-gray-600">Host din side direkte fra Replit (gratis)</p>
                        </div>
                    </div>
                </div>
                <div class="module-card bg-gray-50 p-6 rounded-xl hover:bg-white hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start">
                        <div class="text-3xl mr-4">🎁</div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-bold text-gray-900">Modul 7: Bonus: Udvidelser</h3>
                                <span class="text-sm text-gray-500">15 min</span>
                            </div>
                            <p class="text-gray-600">Kontaktformular, portefølje og mere</p>
                        </div>
                    </div>
                </div>';

    // Replace the PHP foreach block with hardcoded modules
    $hjemmesideHtml = preg_replace(
        '/\s*<\?php foreach \(\$modules as \$module\): \?>.*?<\?php endforeach; \?>/s',
        $moduleCardsHtml,
        $hjemmesideSource
    );

    // Replace startCheckout function to link to courses page
    $hjemmesideHtml = str_replace(
        "window.location.href = '/course-purchase?course_id=<?= \$courseId ?>';",
        "window.location.href = '/courses';",
        $hjemmesideHtml
    );

    // Remove any remaining PHP tags
    $hjemmesideHtml = preg_replace('/<\?=.*?\?>/s', '', $hjemmesideHtml);
    $hjemmesideHtml = preg_replace('/<\?php.*?\?>/s', '', $hjemmesideHtml);

    // Update links
    $hjemmesideHtml = updateLinks($hjemmesideHtml, $linkMap);

    // Fix branding
    $hjemmesideHtml = str_replace('AI BootCamp</title>', 'AI BootCamp HQ</title>', $hjemmesideHtml);
    $hjemmesideHtml = str_replace('alt="AI BootCamp"', 'alt="AI BootCamp HQ"', $hjemmesideHtml);
} else {
    $hjemmesideHtml = '<h1>Homepage - content pending</h1>';
}


// =============================
// INSERT PAGES INTO DATABASE
// =============================

$pages = [
    [
        'slug' => 'gratis',
        'title' => 'Gratis PDF\'er & AI Guides',
        'content' => $gratisHtml,
        'layout' => 'full',
        'meta_description' => 'Download gratis AI guides, prompts og ressourcer. Alt fra 300 AI prompts til Atlas browser guide og mere - helt gratis.',
        'status' => 'published',
        'is_homepage' => false,
        'sort_order' => 1,
    ],
    [
        'slug' => 'foredrag',
        'title' => 'Professionelle Foredrag om AI',
        'content' => $foredragHtml,
        'layout' => 'full',
        'meta_description' => 'Book Jannik Hansen til inspirerende foredrag om AI og digital transformation. Fysisk i hele Europa eller online via Zoom.',
        'status' => 'published',
        'is_homepage' => false,
        'sort_order' => 2,
    ],
    [
        'slug' => 'ai-konsulent',
        'title' => 'AI Konsulenterne - Bliv AI Ekspert',
        'content' => $aiKonsulentHtml,
        'layout' => 'full',
        'meta_description' => 'Lær at blive AI konsulent og sælg skalerbare automation pakker til 8-60k DKK.',
        'status' => 'published',
        'is_homepage' => false,
        'sort_order' => 3,
    ],
    [
        'slug' => 'homepage',
        'title' => 'Byg hjemmeside med AI uden kode',
        'content' => $hjemmesideHtml,
        'layout' => 'full',
        'meta_description' => 'Lær at bygge professionelle hjemmesider med AI og Replit på kun 3 timer.',
        'status' => 'published',
        'is_homepage' => true,
        'sort_order' => 0,
    ],
];

echo "\n=== Inserting custom pages ===\n\n";

foreach ($pages as $page) {
    $contentLen = strlen($page['content']);
    echo "Page: '{$page['slug']}' ({$page['title']})\n";
    echo "  Layout: {$page['layout']}, Status: {$page['status']}, Homepage: " . ($page['is_homepage'] ? 'YES' : 'no') . "\n";
    echo "  Content size: " . number_format($contentLen) . " bytes\n";

    if ($contentLen < 100) {
        echo "  WARNING: Content seems too small, skipping.\n\n";
        continue;
    }

    if ($dryRun) {
        echo "  [DRY RUN] Would insert.\n\n";
        continue;
    }

    // Check if page already exists
    $stmt = $kompazaDb->prepare("SELECT id FROM custom_pages WHERE tenant_id = ? AND slug = ?");
    $stmt->execute([$tenantId, $page['slug']]);
    $existing = $stmt->fetch();

    if ($existing) {
        echo "  Page already exists (id={$existing['id']}), updating...\n";
        $stmt = $kompazaDb->prepare("UPDATE custom_pages SET title = ?, content = ?, layout = ?, meta_description = ?, status = ?, is_homepage = ?, sort_order = ? WHERE id = ?");
        $stmt->execute([
            $page['title'],
            $page['content'],
            $page['layout'],
            $page['meta_description'],
            $page['status'],
            $page['is_homepage'] ? 1 : 0,
            $page['sort_order'],
            $existing['id'],
        ]);
        echo "  Updated.\n\n";
    } else {
        // If this is homepage, clear any existing homepage flag
        if ($page['is_homepage']) {
            $stmt = $kompazaDb->prepare("UPDATE custom_pages SET is_homepage = 0 WHERE tenant_id = ? AND is_homepage = 1");
            $stmt->execute([$tenantId]);
        }

        $stmt = $kompazaDb->prepare("INSERT INTO custom_pages (tenant_id, slug, title, content, layout, meta_description, status, is_homepage, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $tenantId,
            $page['slug'],
            $page['title'],
            $page['content'],
            $page['layout'],
            $page['meta_description'],
            $page['status'],
            $page['is_homepage'] ? 1 : 0,
            $page['sort_order'],
        ]);
        $pageId = $kompazaDb->lastInsertId();
        echo "  Inserted (id=$pageId).\n\n";
    }
}

echo "=== Done! ===\n";
echo "Pages accessible at:\n";
echo "  https://aibootcamphq.kompaza.com/ (homepage)\n";
echo "  https://aibootcamphq.kompaza.com/gratis\n";
echo "  https://aibootcamphq.kompaza.com/foredrag\n";
echo "  https://aibootcamphq.kompaza.com/ai-konsulent\n";
