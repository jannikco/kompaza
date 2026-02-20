<!-- FAQ Hero -->
<section class="relative overflow-hidden hero-gradient py-20">
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-cyan-500/20 rounded-full blur-3xl"></div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4 tracking-tight">
            Frequently Asked Questions
        </h1>
        <p class="text-blue-100/90 text-lg max-w-2xl mx-auto">
            Everything you need to know about Kompaza. Can't find what you're looking for? Reach out to our support team.
        </p>
    </div>

    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
            <path d="M0 60V30C240 0 480 10 720 20C960 30 1200 50 1440 30V60H0Z" fill="#f9fafb"/>
        </svg>
    </div>
</section>

<!-- FAQ Content -->
<section class="py-20 bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <?php
        $categories = [
            [
                'title' => 'Getting Started',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
                'questions' => [
                    [
                        'q' => 'What is Kompaza?',
                        'a' => 'Kompaza is an all-in-one platform for content marketing, lead generation, e-commerce, and LinkedIn automation. It gives you everything you need to grow your business online — blog articles, ebooks, lead magnets, landing pages, a product store, online courses, and ConnectPilot LinkedIn outreach — all from a single dashboard.',
                    ],
                    [
                        'q' => 'How do I sign up?',
                        'a' => 'Click "Get Started Free" on any page to create your account. You\'ll choose a subdomain (e.g., yourcompany.kompaza.com), fill in your details, and you\'re ready to go. A credit card is required to start your free trial.',
                    ],
                    [
                        'q' => 'Is there a free trial?',
                        'a' => 'Yes! Every plan includes a 7-day free trial with full access to all features. Credit card required. You can upgrade, downgrade, or cancel at any time during or after your trial.',
                    ],
                    [
                        'q' => 'What happens after my trial ends?',
                        'a' => 'When your trial ends, you\'ll be prompted to choose a paid plan to continue using Kompaza. Your content and data are preserved, so you can pick up right where you left off. If you don\'t subscribe, your account will be paused until you choose a plan.',
                    ],
                ],
            ],
            [
                'title' => 'Pricing & Billing',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                'questions' => [
                    [
                        'q' => 'What plans are available?',
                        'a' => 'We offer three plans: <strong>Starter</strong> for solopreneurs just getting started, <strong>Growth</strong> for businesses that need the full toolkit including LinkedIn automation and payment processing, and <strong>Enterprise</strong> for agencies and larger teams with advanced needs. Visit our <a href="/pricing" class="text-indigo-600 hover:text-indigo-700 font-medium">pricing page</a> for details.',
                    ],
                    [
                        'q' => 'Can I switch plans?',
                        'a' => 'Absolutely. You can upgrade or downgrade your plan at any time from your account settings. When upgrading, you get immediate access to the new features. When downgrading, the change takes effect at the start of your next billing cycle.',
                    ],
                    [
                        'q' => 'What payment methods do you accept?',
                        'a' => 'We accept all major credit and debit cards (Visa, Mastercard, American Express) through Stripe. All payments are processed securely with industry-standard encryption.',
                    ],
                    [
                        'q' => 'Is there a refund policy?',
                        'a' => 'If you\'re not satisfied within the first 30 days of your paid subscription, contact our support team for a full refund. After 30 days, you can cancel at any time and your access will continue until the end of your current billing period.',
                    ],
                ],
            ],
            [
                'title' => 'Content & Marketing',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
                'questions' => [
                    [
                        'q' => 'How do blog articles work?',
                        'a' => 'Write and publish blog articles directly from your admin dashboard using our rich text editor. Articles are automatically published to your website with SEO-friendly URLs, and you can schedule posts, add featured images, and categorize content to keep your blog organized.',
                    ],
                    [
                        'q' => 'What are lead magnets?',
                        'a' => 'Lead magnets are free resources (like PDF guides, checklists, or templates) that you offer in exchange for a visitor\'s email address. Kompaza lets you create beautiful landing pages for each lead magnet and automatically delivers the download via email when someone signs up.',
                    ],
                    [
                        'q' => 'Can I create ebooks?',
                        'a' => 'Yes! You can publish and sell ebooks directly through your Kompaza store. Upload your PDF, set a price (or offer it for free), add a cover image and description, and it\'s ready for your customers to purchase and download.',
                    ],
                    [
                        'q' => 'How do landing pages work?',
                        'a' => 'Each lead magnet gets its own dedicated landing page with a signup form. When a visitor submits their email, they\'re added to your contact list and receive the lead magnet via a secure, tokenized download link. You can track signups and conversion rates from your dashboard.',
                    ],
                ],
            ],
            [
                'title' => 'Products & Orders',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>',
                'questions' => [
                    [
                        'q' => 'How do I add products?',
                        'a' => 'From your admin dashboard, go to Products and click "Create Product." Add a title, description, images, price, and any other details. Your product is instantly available in your online store. You can also manage inventory, create categories, and set up digital or physical product types.',
                    ],
                    [
                        'q' => 'How does checkout work?',
                        'a' => 'Customers can add products to their cart and complete checkout with a streamlined, secure payment flow. Orders are tracked in your admin dashboard where you can manage order status, view customer details, and handle fulfillment.',
                    ],
                    [
                        'q' => 'What payment processing do you use?',
                        'a' => 'Kompaza uses Stripe for payment processing, the same trusted platform used by millions of businesses worldwide. You connect your own Stripe account in your tenant settings, so payments go directly to you. Stripe handles PCI compliance and supports cards, Apple Pay, and Google Pay.',
                    ],
                ],
            ],
            [
                'title' => 'ConnectPilot (LinkedIn Automation)',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>',
                'questions' => [
                    [
                        'q' => 'What is ConnectPilot?',
                        'a' => 'ConnectPilot is Kompaza\'s built-in LinkedIn automation tool. It helps you automate connection requests and messaging campaigns on LinkedIn, so you can generate leads and build your professional network at scale — all managed from your Kompaza dashboard.',
                    ],
                    [
                        'q' => 'Is it safe to use?',
                        'a' => 'ConnectPilot is designed with safety in mind. It respects LinkedIn\'s daily activity limits and uses conservative defaults (20 connection requests and 50 messages per day). By staying within these limits, you minimize the risk of any account restrictions. That said, automated activity on LinkedIn always carries some risk, so we recommend using it responsibly.',
                    ],
                    [
                        'q' => 'What are the daily limits?',
                        'a' => 'The default limits are 20 connection requests and 50 messages per day, which are well within LinkedIn\'s acceptable usage thresholds. You can adjust these limits in your ConnectPilot settings, but we recommend staying at or below the defaults for optimal account safety.',
                    ],
                    [
                        'q' => 'Do I need LinkedIn Premium?',
                        'a' => 'No, LinkedIn Premium is not required to use ConnectPilot. It works with any LinkedIn account. However, having a Premium or Sales Navigator subscription may give you access to additional LinkedIn features like InMail credits and advanced search filters.',
                    ],
                ],
            ],
            [
                'title' => 'Custom Domain & Branding',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>',
                'questions' => [
                    [
                        'q' => 'Can I use my own domain?',
                        'a' => 'Yes! On the Enterprise plan, you can connect your own custom domain (e.g., shop.yourbrand.com) to your Kompaza site. We provide the DNS configuration instructions, and our team can help you get set up. Custom domains include a free SSL certificate.',
                    ],
                    [
                        'q' => 'How do subdomains work?',
                        'a' => 'When you sign up, you choose a unique subdomain like <strong>yourcompany.kompaza.com</strong>. This is your website address where customers can find your store, blog, ebooks, and lead magnets. You can change your subdomain from your settings if needed.',
                    ],
                ],
            ],
            [
                'title' => 'Support',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>',
                'questions' => [
                    [
                        'q' => 'How do I get help?',
                        'a' => 'You can reach our support team at <a href="mailto:support@kompaza.com" class="text-indigo-600 hover:text-indigo-700 font-medium">support@kompaza.com</a>. We typically respond within 24 hours on business days. Enterprise customers receive priority support with faster response times.',
                    ],
                    [
                        'q' => 'Do you offer onboarding?',
                        'a' => 'Yes! All new customers receive access to our getting-started guides and documentation. Enterprise customers get a dedicated onboarding session with our team to help configure your account, import existing content, and set up integrations so you can hit the ground running.',
                    ],
                ],
            ],
        ];
        ?>

        <?php foreach ($categories as $catIndex => $category): ?>
            <div class="<?= $catIndex > 0 ? 'mt-14' : '' ?>">
                <!-- Category header -->
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <?= $category['icon'] ?>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900"><?= h($category['title']) ?></h2>
                </div>

                <!-- Accordion -->
                <div x-data="{ open: null }" class="space-y-3">
                    <?php foreach ($category['questions'] as $qIndex => $item): ?>
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden transition-shadow"
                             :class="open === <?= $qIndex ?> ? 'shadow-md border-indigo-200' : 'shadow-sm hover:shadow-md'">
                            <button @click="open = open === <?= $qIndex ?> ? null : <?= $qIndex ?>"
                                    class="w-full flex items-center justify-between px-6 py-4 text-left">
                                <span class="text-sm font-semibold text-gray-900 pr-4"><?= h($item['q']) ?></span>
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0 transition-transform duration-200"
                                     :class="open === <?= $qIndex ?> ? 'rotate-180' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open === <?= $qIndex ?>"
                                 x-collapse
                                 x-cloak>
                                <div class="px-6 pb-5 text-sm text-gray-600 leading-relaxed border-t border-gray-100 pt-4">
                                    <?= $item['a'] ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</section>

<!-- CTA Section -->
<section class="bg-white py-20">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">Still have questions?</h2>
        <p class="text-gray-600 mb-8 max-w-xl mx-auto">
            Our team is here to help. Reach out and we'll get back to you as soon as possible.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="mailto:support@kompaza.com"
               class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition duration-200">
                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Contact Support
            </a>
            <a href="/register"
               class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition duration-200 shadow-sm shadow-indigo-600/25 hover:shadow-md hover:shadow-indigo-600/25">
                Get Started Free
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>
</section>
