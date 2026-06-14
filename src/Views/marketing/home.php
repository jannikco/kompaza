<?php
// Feature showcase data (reflects the real platform capabilities)
$features = [
    ['title' => 'Funnels & Landing Pages', 'desc' => 'Drag-ready opt-in, sales, checkout and thank-you pages. Launch a full funnel in minutes.', 'color' => 'indigo',
     'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
    ['title' => 'Online Courses', 'desc' => 'A complete LMS — modules, lessons, video, quizzes, drip and certificates.', 'color' => 'blue',
     'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.247m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.247'],
    ['title' => 'Memberships', 'desc' => 'Recurring plans with tiered, gated content and automatic access control.', 'color' => 'violet',
     'icon' => 'M5 13l4 4L19 7'],
    ['title' => 'Community', 'desc' => 'Channels, posts, comments and likes — keep your audience engaged in one place.', 'color' => 'cyan',
     'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
    ['title' => 'Webinars & Live Q&A', 'desc' => 'Run live or evergreen webinars and tier-gated live sessions with recordings.', 'color' => 'sky',
     'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
    ['title' => 'Checkout, Bumps & Upsells', 'desc' => 'Stripe checkout with order bumps and one-click upsells to lift order value.', 'color' => 'emerald',
     'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
    ['title' => 'Email & Automation', 'desc' => 'Broadcasts, sequences, abandoned-cart recovery and lead-magnet delivery.', 'color' => 'amber',
     'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
    ['title' => 'A/B Testing & Analytics', 'desc' => 'Split-test pages by traffic weight and track views, conversions and revenue.', 'color' => 'rose',
     'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
    ['title' => 'LinkedIn Automation', 'desc' => 'ConnectPilot finds leads, sends connection requests and follows up — on autopilot.', 'color' => 'blue', 'badge' => 'Exclusive',
     'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
];

$colorMap = [
    'indigo' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600'],
    'blue' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600'],
    'violet' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600'],
    'cyan' => ['bg' => 'bg-cyan-50', 'text' => 'text-cyan-600'],
    'sky' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-600'],
    'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
    'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
    'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600'],
];

$comparison = [
    ['Sales funnels & landing pages', true, true],
    ['Order bumps & 1-click upsells', true, true],
    ['A/B testing', true, true],
    ['Email marketing & automation', true, true],
    ['Online courses (full LMS)', true, 'Limited'],
    ['Memberships & gated content', true, true],
    ['Community & discussions', true, false],
    ['Webinars & live Q&A', true, false],
    ['Blog & content CMS', true, false],
    ['LinkedIn outreach automation', true, false],
    ['Your own branded domain', true, true],
];

$testimonials = [
    ['quote' => 'Kompaza replaced ClickFunnels, our course tool and our email platform. One login, one bill, and our funnels actually convert better.', 'name' => 'Morten K.', 'role' => 'Marketing Consultant', 'img' => 'avatar-morten'],
    ['quote' => 'The ConnectPilot LinkedIn automation alone pays for the whole platform. We tripled our connection rate and the follow-ups run themselves.', 'name' => 'Sarah L.', 'role' => 'B2B Sales Manager', 'img' => 'avatar-sarah'],
    ['quote' => 'We launched a paid community, a course and a webinar funnel in a weekend. The stack we cancelled was costing us 4x more.', 'name' => 'Jakob H.', 'role' => 'Founder, Digital Agency', 'img' => 'avatar-jakob'],
];
?>

<!-- ============ HERO ============ -->
<section class="relative overflow-hidden hero-gradient">
    <div class="absolute inset-0 opacity-[0.12]" style="background-image:url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.18&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    <div class="absolute -top-24 left-1/4 w-[28rem] h-[28rem] bg-purple-500/25 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 right-0 w-[28rem] h-[28rem] bg-cyan-400/25 rounded-full blur-3xl"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-28 lg:pt-28">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-8 items-center">
            <!-- Copy -->
            <div class="text-center lg:text-left">
                <div class="inline-flex items-center px-4 py-1.5 bg-white/10 border border-white/20 rounded-full text-sm font-medium text-white/90 mb-6 backdrop-blur-sm">
                    <span class="w-2 h-2 rounded-full bg-cyan-300 mr-2"></span>
                    The all-in-one ClickFunnels alternative
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-[3.4rem] font-extrabold text-white mb-6 leading-[1.08] tracking-tight">
                    Build funnels, sell courses, and grow your
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-300 via-blue-200 to-purple-300"> audience</span>
                </h1>
                <p class="text-lg md:text-xl text-blue-100/90 mb-8 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                    Landing pages, sales funnels, online courses, memberships, communities, webinars, email automation and payments — everything you need to turn visitors into customers. One login. One bill.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                    <a href="/register" class="w-full sm:w-auto px-8 py-4 bg-white text-indigo-700 font-bold rounded-xl transition duration-300 transform hover:scale-105 shadow-xl shadow-black/10 text-center">
                        Start Your Free Trial
                    </a>
                    <a href="#features" class="w-full sm:w-auto px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl transition duration-300 backdrop-blur-sm border border-white/20 text-center">
                        See everything you get &rarr;
                    </a>
                </div>
                <div class="mt-6 flex flex-wrap items-center justify-center lg:justify-start gap-x-6 gap-y-2 text-sm text-blue-100/80">
                    <span class="inline-flex items-center"><svg class="w-4 h-4 mr-1.5 text-cyan-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>7-day free trial</span>
                    <span class="inline-flex items-center"><svg class="w-4 h-4 mr-1.5 text-cyan-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Replace 6+ tools</span>
                    <span class="inline-flex items-center"><svg class="w-4 h-4 mr-1.5 text-cyan-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Cancel anytime</span>
                </div>
            </div>

            <!-- Product mockup: funnel builder -->
            <div class="relative lg:pl-6">
                <div class="absolute -inset-4 bg-white/10 rounded-3xl blur-2xl"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl ring-1 ring-black/5 overflow-hidden">
                    <!-- Browser chrome -->
                    <div class="flex items-center gap-2 px-4 py-3 bg-gray-100 border-b border-gray-200">
                        <span class="w-3 h-3 rounded-full bg-red-400"></span>
                        <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                        <span class="w-3 h-3 rounded-full bg-green-400"></span>
                        <div class="ml-3 flex-1 bg-white rounded-md px-3 py-1 text-xs text-gray-400 border border-gray-200 truncate">app.kompaza.com/funnels/spring-launch</div>
                    </div>
                    <!-- Canvas -->
                    <div class="p-5 bg-gray-50">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-bold text-gray-900">Spring Launch Funnel</p>
                                <p class="text-xs text-gray-400">Live · 4 steps</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">+38% conv.</span>
                        </div>
                        <!-- Funnel steps -->
                        <div class="grid grid-cols-4 gap-2">
                            <?php
                            $steps = [
                                ['Opt-in', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'indigo', '62%'],
                                ['Sales', 'M13 10V3L4 14h7v7l9-11h-7z', 'blue', '41%'],
                                ['Checkout', 'M3 3h2l.4 2M7 13h10l4-8H5.4', 'emerald', '28%'],
                                ['Upsell', 'M5 10l7-7m0 0l7 7m-7-7v18', 'violet', '19%'],
                            ];
                            foreach ($steps as $s): ?>
                                <div class="bg-white rounded-lg border border-gray-200 p-2.5 text-center">
                                    <div class="w-8 h-8 mx-auto rounded-lg bg-<?= $s[2] ?>-50 flex items-center justify-center mb-1.5">
                                        <svg class="w-4 h-4 text-<?= $s[2] ?>-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $s[1] ?>"/></svg>
                                    </div>
                                    <p class="text-[11px] font-semibold text-gray-700"><?= $s[0] ?></p>
                                    <p class="text-[10px] text-gray-400"><?= $s[3] ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Mini stats -->
                        <div class="grid grid-cols-3 gap-2 mt-3">
                            <div class="bg-white rounded-lg border border-gray-200 p-3">
                                <p class="text-[10px] text-gray-400 uppercase tracking-wide">Visitors</p>
                                <p class="text-base font-bold text-gray-900">8,420</p>
                            </div>
                            <div class="bg-white rounded-lg border border-gray-200 p-3">
                                <p class="text-[10px] text-gray-400 uppercase tracking-wide">Sales</p>
                                <p class="text-base font-bold text-gray-900">1,602</p>
                            </div>
                            <div class="bg-white rounded-lg border border-gray-200 p-3">
                                <p class="text-[10px] text-gray-400 uppercase tracking-wide">Revenue</p>
                                <p class="text-base font-bold text-emerald-600">$48.9k</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Floating badge -->
                <div class="absolute -bottom-4 -left-4 bg-white rounded-xl shadow-lg ring-1 ring-black/5 px-4 py-3 hidden sm:flex items-center gap-2">
                    <span class="w-9 h-9 rounded-lg bg-cyan-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </span>
                    <div>
                        <p class="text-xs text-gray-400 leading-none">New signup</p>
                        <p class="text-sm font-bold text-gray-900 leading-tight">+1 just now</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full"><path d="M0 80V40C240 0 480 0 720 20C960 40 1200 60 1440 40V80H0Z" fill="white"/></svg>
    </div>
</section>

<!-- ============ REPLACES YOUR STACK ============ -->
<section class="bg-white pt-4 pb-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-gray-500 text-sm font-medium mb-6">One subscription replaces your whole stack</p>
        <div class="flex flex-wrap items-center justify-center gap-3">
            <?php foreach (['ClickFunnels','Kajabi','Teachable','Circle','Mailchimp','Calendly','Substack'] as $tool): ?>
                <span class="inline-flex items-center px-4 py-2 rounded-full bg-gray-50 border border-gray-200 text-gray-400 text-sm font-semibold line-through decoration-rose-400/70"><?= $tool ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ FEATURES ============ -->
<section id="features" class="py-20 lg:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 max-w-3xl mx-auto">
            <p class="text-indigo-600 font-semibold text-sm uppercase tracking-wider mb-3">Everything in one platform</p>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Ten products. One login. One bill.</h2>
            <p class="text-gray-600 text-lg leading-relaxed">Stop duct-taping five tools together. Kompaza brings your funnels, courses, community and marketing under one roof — so your data, your audience and your revenue all live in one place.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($features as $f): $c = $colorMap[$f['color']]; ?>
                <div class="group relative bg-white rounded-2xl p-7 border border-gray-200 hover:border-indigo-200 hover:shadow-lg transition-all duration-300">
                    <?php if (!empty($f['badge'])): ?>
                        <span class="absolute top-5 right-5 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gradient-to-r from-cyan-500 to-blue-500 text-white"><?= h($f['badge']) ?></span>
                    <?php endif; ?>
                    <div class="w-12 h-12 <?= $c['bg'] ?> rounded-xl flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 <?= $c['text'] ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $f['icon'] ?>"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2"><?= h($f['title']) ?></h3>
                    <p class="text-gray-600 text-sm leading-relaxed"><?= h($f['desc']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ FUNNEL HIGHLIGHT (dark, brand visual) ============ -->
<section class="relative overflow-hidden bg-gray-950 py-20 lg:py-28">
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-950/60 via-gray-950 to-gray-950"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="order-2 lg:order-1">
                <p class="text-cyan-400 font-semibold text-sm uppercase tracking-wider mb-3">Funnels that convert</p>
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-5 leading-tight">Every step engineered to turn clicks into customers</h2>
                <p class="text-gray-300 text-lg mb-8 leading-relaxed">Drag together opt-in, sales, checkout, upsell and thank-you pages. Add order bumps and one-click upsells, split-test variants by traffic weight, and watch conversions and revenue update in real time.</p>
                <ul class="space-y-4">
                    <?php foreach ([
                        'Order bumps & one-click upsells to grow average order value',
                        'A/B test any page and let the winner take the traffic',
                        'Abandoned-cart recovery and email follow-up built in',
                        'Real-time funnel analytics — views, conversions, revenue',
                    ] as $point): ?>
                        <li class="flex items-start text-gray-200">
                            <svg class="w-6 h-6 text-cyan-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span><?= h($point) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <a href="/register" class="inline-flex items-center mt-8 px-7 py-3.5 bg-white text-gray-900 font-bold rounded-xl hover:scale-105 transition transform">Build your first funnel free &rarr;</a>
            </div>
            <div class="order-1 lg:order-2 relative">
                <div class="absolute inset-0 bg-gradient-to-tr from-indigo-500/30 to-cyan-500/30 rounded-3xl blur-3xl"></div>
                <img src="/images/marketing/brand-funnel.jpg" alt="Kompaza sales funnel" class="relative rounded-2xl shadow-2xl ring-1 ring-white/10 w-full" loading="lazy">
            </div>
        </div>
    </div>
</section>

<!-- ============ COMPARISON ============ -->
<section class="py-20 lg:py-24 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 max-w-2xl mx-auto">
            <p class="text-indigo-600 font-semibold text-sm uppercase tracking-wider mb-3">Why switch</p>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">More than a funnel builder</h2>
            <p class="text-gray-600 text-lg">ClickFunnels builds funnels. Kompaza builds your whole business — funnels, courses, community and outreach — usually for a lot less.</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="grid grid-cols-12 bg-gray-900 text-white text-sm font-semibold">
                <div class="col-span-6 px-5 py-4">Feature</div>
                <div class="col-span-3 px-3 py-4 text-center bg-indigo-600">Kompaza</div>
                <div class="col-span-3 px-3 py-4 text-center text-gray-300">ClickFunnels</div>
            </div>
            <?php foreach ($comparison as $i => $row): ?>
                <div class="grid grid-cols-12 items-center text-sm <?= $i % 2 ? 'bg-gray-50/60' : 'bg-white' ?>">
                    <div class="col-span-6 px-5 py-3.5 text-gray-700 font-medium"><?= h($row[0]) ?></div>
                    <div class="col-span-3 px-3 py-3.5 text-center">
                        <?php if ($row[1] === true): ?>
                            <svg class="w-5 h-5 text-indigo-600 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <?php else: ?>
                            <span class="text-xs font-semibold text-amber-600"><?= h($row[1]) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="col-span-3 px-3 py-3.5 text-center">
                        <?php if ($row[2] === true): ?>
                            <svg class="w-5 h-5 text-gray-400 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <?php elseif ($row[2] === false): ?>
                            <svg class="w-5 h-5 text-gray-300 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        <?php else: ?>
                            <span class="text-xs font-semibold text-gray-400"><?= h($row[2]) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="grid grid-cols-12 items-center text-sm border-t-2 border-gray-100 bg-white">
                <div class="col-span-6 px-5 py-4 text-gray-900 font-bold">Starting price</div>
                <div class="col-span-3 px-3 py-4 text-center font-extrabold text-indigo-600"><?= $fromPrice ? '$' . number_format($fromPrice) . '/mo' : 'See pricing' ?></div>
                <div class="col-span-3 px-3 py-4 text-center font-semibold text-gray-500">from $97/mo</div>
            </div>
        </div>
        <p class="text-center text-xs text-gray-400 mt-4">ClickFunnels pricing per their public plans. Comparison reflects standard included features.</p>
    </div>
</section>

<!-- ============ HOW IT WORKS ============ -->
<section class="py-20 lg:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 max-w-3xl mx-auto">
            <p class="text-indigo-600 font-semibold text-sm uppercase tracking-wider mb-3">Live in minutes</p>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Launch on your own branded site</h2>
            <p class="text-gray-600 text-lg">Get a branded subdomain (or bring your own domain) and start selling today — no developers, no plugins.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <?php
            $stepsHow = [
                ['1', 'indigo', 'Sign up', 'Create your account and claim your subdomain in under a minute.'],
                ['2', 'blue', 'Build your funnel', 'Add pages, courses, products and memberships from one clean admin.'],
                ['3', 'cyan', 'Grow on autopilot', 'Capture leads, take payments, automate email and LinkedIn outreach.'],
            ];
            foreach ($stepsHow as $s): ?>
                <div class="text-center">
                    <div class="w-16 h-16 bg-<?= $s[1] ?>-600 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-<?= $s[1] ?>-600/25">
                        <span class="text-2xl font-bold text-white"><?= $s[0] ?></span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2"><?= h($s[2]) ?></h3>
                    <p class="text-gray-600 text-sm leading-relaxed"><?= h($s[3]) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ TESTIMONIALS ============ -->
<section class="py-20 lg:py-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <p class="text-gray-500 font-medium text-sm uppercase tracking-wider">Loved by founders & marketers</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <?php foreach ($testimonials as $t): ?>
                <div class="bg-white rounded-2xl p-8 border border-gray-100 shadow-sm flex flex-col">
                    <div class="flex items-center mb-4">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <?php endfor; ?>
                    </div>
                    <p class="text-gray-700 leading-relaxed mb-6 flex-1">&ldquo;<?= h($t['quote']) ?>&rdquo;</p>
                    <div class="flex items-center">
                        <img src="/images/marketing/<?= h($t['img']) ?>.jpg" alt="<?= h($t['name']) ?>" class="w-11 h-11 rounded-full object-cover" loading="lazy">
                        <div class="ml-3">
                            <p class="text-sm font-semibold text-gray-900"><?= h($t['name']) ?></p>
                            <p class="text-xs text-gray-500"><?= h($t['role']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ FINAL CTA ============ -->
<section class="py-20 lg:py-24 hero-gradient relative overflow-hidden">
    <div class="absolute -top-10 left-1/3 w-96 h-96 bg-purple-500/25 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 right-1/3 w-96 h-96 bg-cyan-400/25 rounded-full blur-3xl"></div>
    <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Your whole business, one platform</h2>
        <p class="text-blue-100/90 text-lg mb-9 max-w-xl mx-auto">Start your 7-day free trial and launch your first funnel today. Cancel anytime.</p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/register" class="w-full sm:w-auto px-10 py-4 bg-white text-indigo-700 font-bold rounded-xl transition duration-300 transform hover:scale-105 shadow-xl text-center">Start Your Free Trial</a>
            <a href="/pricing" class="w-full sm:w-auto px-10 py-4 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl transition duration-300 border border-white/20 text-center">View Plans &amp; Pricing</a>
        </div>
    </div>
</section>
