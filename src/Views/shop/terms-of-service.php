<?php
$pageTitle = 'Terms of Service';
$tenant = currentTenant();
$companyName = $tenant['company_name'] ?? $tenant['name'] ?? 'Our Company';
$metaDescription = 'Terms of Service for ' . $companyName;

ob_start();
?>

<section class="py-16 lg:py-24">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Terms of Service</h1>

        <div class="prose prose-gray max-w-none space-y-6 text-gray-700">
            <p class="text-sm text-gray-500">Last updated: <?= date('F j, Y') ?></p>

            <h2 class="text-xl font-semibold text-gray-900">1. Acceptance of Terms</h2>
            <p>By accessing and using the services provided by <?= h($companyName) ?>, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our services.</p>

            <h2 class="text-xl font-semibold text-gray-900">2. Account Registration</h2>
            <p>To access certain features, you may need to create an account. You are responsible for maintaining the confidentiality of your account credentials and for all activities under your account. You agree to provide accurate and complete information during registration.</p>

            <h2 class="text-xl font-semibold text-gray-900">3. Purchases and Payments</h2>
            <p>All purchases are subject to product availability. Prices are displayed in the applicable currency and may change without notice. Payment is processed securely through our payment provider. By making a purchase, you agree to pay the full price as displayed.</p>

            <h2 class="text-xl font-semibold text-gray-900">4. Digital Products and Courses</h2>
            <p>Digital products, including ebooks, courses, and downloadable materials, are licensed for personal use only. You may not redistribute, resell, or share access to purchased digital content without written permission.</p>

            <h2 class="text-xl font-semibold text-gray-900">5. Certificates</h2>
            <p>Certificates of completion are awarded upon meeting the required criteria (such as passing quizzes). Certificates can be verified through our public verification system. We reserve the right to revoke certificates if criteria were not legitimately met.</p>

            <h2 class="text-xl font-semibold text-gray-900">6. Refund Policy</h2>
            <p>We offer refunds in accordance with applicable consumer protection laws. For digital products that have been accessed or downloaded, refunds may be limited. Please contact us if you have concerns about a purchase.</p>

            <h2 class="text-xl font-semibold text-gray-900">7. Intellectual Property</h2>
            <p>All content, including text, images, videos, and course materials, is the property of <?= h($companyName) ?> or its licensors and is protected by intellectual property laws. Unauthorized use, reproduction, or distribution is prohibited.</p>

            <h2 class="text-xl font-semibold text-gray-900">8. User Conduct</h2>
            <p>You agree not to use our services for any unlawful purpose, attempt to gain unauthorized access, interfere with the operation of our services, or upload harmful content.</p>

            <h2 class="text-xl font-semibold text-gray-900">9. Limitation of Liability</h2>
            <p><?= h($companyName) ?> shall not be liable for any indirect, incidental, special, or consequential damages arising from the use of our services. Our total liability is limited to the amount paid for the specific product or service in question.</p>

            <h2 class="text-xl font-semibold text-gray-900">10. Changes to Terms</h2>
            <p>We reserve the right to modify these terms at any time. Continued use of our services after changes constitutes acceptance of the updated terms.</p>

            <h2 class="text-xl font-semibold text-gray-900">11. Contact</h2>
            <p>If you have questions about these Terms of Service, please <a href="/contact" class="text-brand hover:underline">contact us</a>.</p>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
