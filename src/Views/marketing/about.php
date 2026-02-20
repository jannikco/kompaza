<style>
    @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-20px); } }
    @keyframes float-delayed { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-14px); } }
    @keyframes pulse-slow { 0%, 100% { opacity: 0.4; } 50% { opacity: 0.8; } }
    @keyframes draw { from { stroke-dashoffset: 1000; } to { stroke-dashoffset: 0; } }
    @keyframes gradient-shift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
    @keyframes fade-up { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float-delayed 7s ease-in-out infinite; animation-delay: 1s; }
    .animate-pulse-slow { animation: pulse-slow 4s ease-in-out infinite; }
    .animate-gradient { background-size: 200% 200%; animation: gradient-shift 8s ease infinite; }
    .fade-up { animation: fade-up 0.8s ease-out forwards; }
    .fade-up-d1 { animation: fade-up 0.8s ease-out 0.1s forwards; opacity: 0; }
    .fade-up-d2 { animation: fade-up 0.8s ease-out 0.2s forwards; opacity: 0; }
    .fade-up-d3 { animation: fade-up 0.8s ease-out 0.3s forwards; opacity: 0; }
    .value-card { transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
    .value-card:hover { transform: translateY(-4px); }
    .value-card:hover .icon-glow { opacity: 1; }
    .icon-glow { transition: opacity 0.5s ease; opacity: 0; }
    .belief-line { position: relative; }
    .belief-line::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 2px; background: linear-gradient(to bottom, #6366f1, #818cf8); border-radius: 1px; }
</style>

<!-- Hero -->
<section class="relative overflow-hidden bg-[#0a0a1a] min-h-[80vh] flex items-center">
    <!-- Abstract background art -->
    <div class="absolute inset-0">
        <div class="absolute top-1/4 left-1/4 w-[600px] h-[600px] bg-indigo-600/10 rounded-full blur-[128px] animate-pulse-slow"></div>
        <div class="absolute bottom-1/4 right-1/4 w-[500px] h-[500px] bg-violet-600/8 rounded-full blur-[128px] animate-pulse-slow" style="animation-delay: 2s;"></div>
    </div>

    <!-- Floating geometric SVGs -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <svg class="absolute top-20 right-[15%] w-16 h-16 text-indigo-500/20 animate-float" viewBox="0 0 64 64" fill="none">
            <path d="M32 4L58 18V46L32 60L6 46V18L32 4Z" stroke="currentColor" stroke-width="1.5"/>
            <path d="M32 4V60M6 18L58 46M58 18L6 46" stroke="currentColor" stroke-width="0.5" opacity="0.5"/>
        </svg>
        <svg class="absolute top-1/3 left-[10%] w-12 h-12 text-violet-400/15 animate-float-delayed" viewBox="0 0 48 48" fill="none">
            <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="1"/>
            <circle cx="24" cy="24" r="12" stroke="currentColor" stroke-width="0.75" opacity="0.6"/>
            <circle cx="24" cy="24" r="4" fill="currentColor" opacity="0.3"/>
        </svg>
        <svg class="absolute bottom-1/4 right-[8%] w-20 h-20 text-amber-400/10 animate-float" style="animation-delay: 3s;" viewBox="0 0 80 80" fill="none">
            <path d="M40 8L72 24V56L40 72L8 56V24L40 8Z" stroke="currentColor" stroke-width="1"/>
            <path d="M40 8L40 72" stroke="currentColor" stroke-width="0.5" opacity="0.4"/>
        </svg>
    </div>

    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-32 lg:py-40 text-center">
        <div class="fade-up">
            <p class="text-indigo-400 text-sm font-semibold tracking-[0.2em] uppercase mb-8">About Kompaza</p>
        </div>
        <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold text-white mb-8 leading-[1.1] tracking-tight fade-up-d1">
            Where creators turn<br>
            knowledge into
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-yellow-200 to-amber-400 animate-gradient"> gold</span>
        </h1>
        <p class="text-xl md:text-2xl text-gray-400 max-w-2xl mx-auto leading-relaxed font-light fade-up-d2">
            Most platforms help you upload content.<br class="hidden md:block">
            We help you build assets.
        </p>
    </div>

    <!-- Bottom fade -->
    <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-white to-transparent"></div>
</section>

<!-- The Name -->
<section class="py-28 lg:py-36 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-20">
            <p class="text-indigo-600 text-sm font-semibold tracking-[0.15em] uppercase mb-4">Our name</p>
            <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 tracking-tight">What's in a name?</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 max-w-5xl mx-auto">
            <!-- Kom -->
            <div class="relative group">
                <div class="absolute -inset-0.5 bg-gradient-to-br from-indigo-100 to-blue-50 rounded-3xl opacity-0 group-hover:opacity-100 transition duration-500"></div>
                <div class="relative bg-white border border-gray-100 rounded-3xl p-10 lg:p-12 hover:border-gray-200 transition duration-500">
                    <!-- Custom SVG: Connected nodes representing community -->
                    <div class="w-20 h-20 mb-8">
                        <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="40" cy="20" r="8" fill="#EEF2FF" stroke="#6366f1" stroke-width="1.5"/>
                            <circle cx="40" cy="20" r="3" fill="#6366f1" opacity="0.6"/>
                            <circle cx="18" cy="55" r="8" fill="#EEF2FF" stroke="#6366f1" stroke-width="1.5"/>
                            <circle cx="18" cy="55" r="3" fill="#6366f1" opacity="0.6"/>
                            <circle cx="62" cy="55" r="8" fill="#EEF2FF" stroke="#6366f1" stroke-width="1.5"/>
                            <circle cx="62" cy="55" r="3" fill="#6366f1" opacity="0.6"/>
                            <path d="M35 27L23 48" stroke="#6366f1" stroke-width="1.5" stroke-linecap="round" opacity="0.4"/>
                            <path d="M45 27L57 48" stroke="#6366f1" stroke-width="1.5" stroke-linecap="round" opacity="0.4"/>
                            <path d="M26 55H54" stroke="#6366f1" stroke-width="1.5" stroke-linecap="round" opacity="0.4"/>
                            <circle cx="40" cy="40" r="4" fill="#6366f1" opacity="0.15"/>
                            <circle cx="40" cy="40" r="1.5" fill="#6366f1" opacity="0.3"/>
                        </svg>
                    </div>
                    <div class="flex items-baseline gap-3 mb-3">
                        <h3 class="text-3xl font-extrabold text-gray-900 tracking-tight">Kom</h3>
                        <span class="text-sm text-indigo-500 font-medium">/kɒm/</span>
                    </div>
                    <p class="text-sm text-indigo-600 font-medium mb-5">Danish for "come" — as in come together</p>
                    <p class="text-gray-500 leading-relaxed text-[17px]">Community. Collaboration. The idea that creators don't have to build alone — and shouldn't.</p>
                </div>
            </div>

            <!-- Paz -->
            <div class="relative group">
                <div class="absolute -inset-0.5 bg-gradient-to-br from-amber-50 to-yellow-50 rounded-3xl opacity-0 group-hover:opacity-100 transition duration-500"></div>
                <div class="relative bg-white border border-gray-100 rounded-3xl p-10 lg:p-12 hover:border-gray-200 transition duration-500">
                    <!-- Custom SVG: Geometric crystal/gem representing refined gold -->
                    <div class="w-20 h-20 mb-8">
                        <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M40 8L62 28L40 72L18 28L40 8Z" fill="#FFFBEB" stroke="#D97706" stroke-width="1.5" stroke-linejoin="round"/>
                            <path d="M18 28H62" stroke="#D97706" stroke-width="1" opacity="0.4"/>
                            <path d="M40 8L40 72" stroke="#D97706" stroke-width="0.75" opacity="0.3"/>
                            <path d="M40 8L30 28L40 72" stroke="#D97706" stroke-width="0.75" opacity="0.25"/>
                            <path d="M40 8L50 28L40 72" stroke="#D97706" stroke-width="0.75" opacity="0.25"/>
                            <path d="M18 28L40 72L62 28" fill="#F59E0B" opacity="0.08"/>
                            <path d="M30 28L40 8L50 28" fill="#F59E0B" opacity="0.12"/>
                            <circle cx="40" cy="36" r="5" fill="#F59E0B" opacity="0.15"/>
                            <circle cx="40" cy="36" r="2" fill="#D97706" opacity="0.3"/>
                        </svg>
                    </div>
                    <div class="flex items-baseline gap-3 mb-3">
                        <h3 class="text-3xl font-extrabold text-gray-900 tracking-tight">Paz</h3>
                        <span class="text-sm text-amber-600 font-medium">/pɑːz/</span>
                    </div>
                    <p class="text-sm text-amber-600 font-medium mb-5">Inspired by refined gold — the purest form</p>
                    <p class="text-gray-500 leading-relaxed text-[17px]">Your knowledge, refined. Your content, elevated. Your expertise, turned into something valuable and lasting.</p>
                </div>
            </div>
        </div>

        <div class="text-center mt-16">
            <p class="text-2xl font-semibold text-gray-900 tracking-tight">Together, we create <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-500 via-yellow-400 to-amber-500 animate-gradient">gold</span>.</p>
        </div>
    </div>
</section>

<!-- Why We Exist -->
<section class="py-28 lg:py-36 bg-gray-50/50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mb-20">
            <p class="text-indigo-600 text-sm font-semibold tracking-[0.15em] uppercase mb-4">Why we exist</p>
            <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 tracking-tight mb-6">Built for creators who think long-term</h2>
            <p class="text-xl text-gray-500 leading-relaxed">The creator economy is full of noise. We built Kompaza for those who want to own their audience, not rent it.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Ownership -->
            <div class="value-card bg-white rounded-2xl p-10 border border-gray-100 relative overflow-hidden group">
                <div class="icon-glow absolute top-8 right-8 w-32 h-32 bg-indigo-500/5 rounded-full blur-2xl"></div>
                <div class="relative">
                    <div class="w-14 h-14 mb-7">
                        <svg viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="8" y="16" width="40" height="32" rx="4" fill="#EEF2FF" stroke="#6366f1" stroke-width="1.5"/>
                            <path d="M28 28V40" stroke="#6366f1" stroke-width="1.5" stroke-linecap="round"/>
                            <circle cx="28" cy="33" r="6" stroke="#6366f1" stroke-width="1.5" fill="none"/>
                            <circle cx="28" cy="33" r="2" fill="#6366f1" opacity="0.5"/>
                            <path d="M20 16V12C20 7.58 23.58 4 28 4C32.42 4 36 7.58 36 12V16" stroke="#6366f1" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                            <path d="M28 30V36" stroke="#6366f1" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 tracking-tight">Ownership</h3>
                    <p class="text-gray-500 leading-relaxed">Your content. Your audience. Your data. No middleman, no algorithm deciding who sees your work.</p>
                </div>
            </div>

            <!-- Infrastructure -->
            <div class="value-card bg-white rounded-2xl p-10 border border-gray-100 relative overflow-hidden group">
                <div class="icon-glow absolute top-8 right-8 w-32 h-32 bg-violet-500/5 rounded-full blur-2xl"></div>
                <div class="relative">
                    <div class="w-14 h-14 mb-7">
                        <svg viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="6" y="38" width="16" height="12" rx="2" fill="#F5F3FF" stroke="#7c3aed" stroke-width="1.5"/>
                            <rect x="20" y="26" width="16" height="24" rx="2" fill="#F5F3FF" stroke="#7c3aed" stroke-width="1.5"/>
                            <rect x="34" y="14" width="16" height="36" rx="2" fill="#F5F3FF" stroke="#7c3aed" stroke-width="1.5"/>
                            <path d="M14 44H14.01M28 44H28.01M42 44H42.01" stroke="#7c3aed" stroke-width="2" stroke-linecap="round"/>
                            <path d="M14 41H14.01M28 38H28.01M42 38H42.01" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" opacity="0.4"/>
                            <path d="M28 32H28.01M42 32H42.01" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" opacity="0.4"/>
                            <path d="M42 26H42.01M42 20H42.01" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" opacity="0.4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 tracking-tight">Infrastructure</h3>
                    <p class="text-gray-500 leading-relaxed">Not just a publishing tool — a complete system for landing pages, lead capture, and digital product delivery.</p>
                </div>
            </div>

            <!-- Systems -->
            <div class="value-card bg-white rounded-2xl p-10 border border-gray-100 relative overflow-hidden group">
                <div class="icon-glow absolute top-8 right-8 w-32 h-32 bg-emerald-500/5 rounded-full blur-2xl"></div>
                <div class="relative">
                    <div class="w-14 h-14 mb-7">
                        <svg viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M28 8C14 8 8 16 8 28C8 40 14 48 28 48C42 48 48 40 48 28C48 16 42 8 28 8Z" fill="none"/>
                            <path d="M10 28C10 20 16 10 28 10C36 10 44 16 44 28" stroke="#059669" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                            <path d="M46 28C46 36 40 46 28 46C20 46 12 40 12 28" stroke="#059669" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                            <circle cx="10" cy="28" r="3" fill="#ECFDF5" stroke="#059669" stroke-width="1.5"/>
                            <circle cx="46" cy="28" r="3" fill="#ECFDF5" stroke="#059669" stroke-width="1.5"/>
                            <circle cx="28" cy="28" r="5" fill="#ECFDF5" stroke="#059669" stroke-width="1.5"/>
                            <circle cx="28" cy="28" r="2" fill="#059669" opacity="0.4"/>
                            <path d="M44 28L49 24M44 28L49 32" stroke="#059669" stroke-width="1" stroke-linecap="round" opacity="0.5"/>
                            <path d="M12 28L7 32M12 28L7 24" stroke="#059669" stroke-width="1" stroke-linecap="round" opacity="0.5"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 tracking-tight">Systems</h3>
                    <p class="text-gray-500 leading-relaxed">Repeatable, scalable workflows that grow your audience while you focus on creating.</p>
                </div>
            </div>

            <!-- Revenue -->
            <div class="value-card bg-white rounded-2xl p-10 border border-gray-100 relative overflow-hidden group">
                <div class="icon-glow absolute top-8 right-8 w-32 h-32 bg-amber-500/5 rounded-full blur-2xl"></div>
                <div class="relative">
                    <div class="w-14 h-14 mb-7">
                        <svg viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 44L18 34L26 38L36 24L48 12" stroke="#D97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M38 12H48V22" stroke="#D97706" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8 44L18 34L26 38L36 24L48 12" stroke="none" fill="url(#revenue-gradient)" opacity="0.12"/>
                            <path d="M8 44L18 34L26 38L36 24L48 12V44H8Z" fill="url(#revenue-gradient)" opacity="0.06"/>
                            <circle cx="18" cy="34" r="2.5" fill="#FFFBEB" stroke="#D97706" stroke-width="1"/>
                            <circle cx="26" cy="38" r="2.5" fill="#FFFBEB" stroke="#D97706" stroke-width="1"/>
                            <circle cx="36" cy="24" r="2.5" fill="#FFFBEB" stroke="#D97706" stroke-width="1"/>
                            <defs>
                                <linearGradient id="revenue-gradient" x1="8" y1="12" x2="8" y2="44">
                                    <stop stop-color="#F59E0B"/>
                                    <stop offset="1" stop-color="#F59E0B" stop-opacity="0"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 tracking-tight">Revenue</h3>
                    <p class="text-gray-500 leading-relaxed">Built-in monetization from day one. Sell ebooks, capture leads, and build a real business around your knowledge.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- What We Believe -->
<section class="py-28 lg:py-36 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 lg:gap-24 items-center">
            <div>
                <p class="text-indigo-600 text-sm font-semibold tracking-[0.15em] uppercase mb-4">What we believe</p>
                <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 tracking-tight mb-8">Principles that guide everything we build</h2>
                <p class="text-lg text-gray-500 leading-relaxed">We're not building another content management system. We're building the operating system for independent creators who think like entrepreneurs.</p>
            </div>

            <div class="space-y-6">
                <div class="belief-line pl-6 py-1">
                    <p class="text-lg text-gray-700 leading-relaxed">Creators should <strong class="text-gray-900 font-semibold">own</strong> their platform, not rent it.</p>
                </div>
                <div class="belief-line pl-6 py-1">
                    <p class="text-lg text-gray-700 leading-relaxed">Content is an <strong class="text-gray-900 font-semibold">asset</strong>, not a post.</p>
                </div>
                <div class="belief-line pl-6 py-1">
                    <p class="text-lg text-gray-700 leading-relaxed">The best creators think in <strong class="text-gray-900 font-semibold">systems</strong>, not one-offs.</p>
                </div>
                <div class="belief-line pl-6 py-1">
                    <p class="text-lg text-gray-700 leading-relaxed">A lead magnet is more valuable than a <strong class="text-gray-900 font-semibold">viral moment</strong>.</p>
                </div>
                <div class="belief-line pl-6 py-1">
                    <p class="text-lg text-gray-700 leading-relaxed">You don't need a huge audience — you need the <strong class="text-gray-900 font-semibold">right</strong> one.</p>
                </div>
            </div>
        </div>

        <!-- Quote -->
        <div class="mt-24 text-center">
            <div class="inline-flex items-center gap-6">
                <div class="hidden md:block w-16 h-px bg-gradient-to-r from-transparent to-amber-300"></div>
                <p class="text-2xl md:text-3xl font-light text-gray-900 italic tracking-tight">"Gold is not discovered. It is refined."</p>
                <div class="hidden md:block w-16 h-px bg-gradient-to-l from-transparent to-amber-300"></div>
            </div>
        </div>
    </div>
</section>

<!-- Mission -->
<section class="relative overflow-hidden bg-[#0a0a1a]">
    <!-- Ambient glow -->
    <div class="absolute inset-0">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-indigo-600/8 rounded-full blur-[100px]"></div>
    </div>

    <!-- Grid overlay -->
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: linear-gradient(rgba(255,255,255,.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.1) 1px, transparent 1px); background-size: 60px 60px;"></div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-32 lg:py-40 text-center">
        <!-- Abstract crown/gem icon -->
        <div class="w-16 h-16 mx-auto mb-10">
            <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M32 4L44 20L56 12L48 36H16L8 12L20 20L32 4Z" stroke="#F59E0B" stroke-width="1.5" stroke-linejoin="round" fill="#F59E0B" fill-opacity="0.08"/>
                <path d="M16 36H48V40C48 44.42 44.42 48 40 48H24C19.58 48 16 44.42 16 40V36Z" stroke="#F59E0B" stroke-width="1.5" fill="#F59E0B" fill-opacity="0.05"/>
                <path d="M32 4V20M20 20L32 36M44 20L32 36" stroke="#F59E0B" stroke-width="0.75" opacity="0.3"/>
                <circle cx="32" cy="42" r="3" fill="#F59E0B" opacity="0.3"/>
            </svg>
        </div>

        <p class="text-indigo-400 text-sm font-semibold tracking-[0.2em] uppercase mb-8">Our mission</p>
        <h2 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-white mb-8 tracking-tight leading-tight">
            Give every creator the tools to build a real business
        </h2>
        <p class="text-xl text-gray-400 max-w-2xl mx-auto leading-relaxed font-light mb-16">
            Without needing a dev team, a marketing department, or a million followers. Just your expertise, your voice, and a platform that works as hard as you do.
        </p>

        <div class="inline-flex flex-col items-center gap-8">
            <div class="w-px h-12 bg-gradient-to-b from-transparent via-indigo-500/50 to-transparent"></div>
            <p class="text-2xl md:text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-yellow-200 to-amber-400 animate-gradient tracking-tight">Welcome to Kompaza.</p>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-24 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight mb-6">Ready to build something that lasts?</h2>
        <p class="text-lg text-gray-500 mb-10">Start your 14-day free trial. No credit card required.</p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/register" class="inline-flex items-center px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white text-base font-semibold rounded-xl transition duration-200 shadow-lg shadow-indigo-600/20 hover:shadow-xl hover:shadow-indigo-600/25">
                Get Started Free
                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <a href="/pricing" class="inline-flex items-center px-8 py-4 bg-gray-50 hover:bg-gray-100 text-gray-700 text-base font-semibold rounded-xl transition duration-200 border border-gray-200">
                View Pricing
            </a>
        </div>
    </div>
</section>
