<?php
$pageTitle = 'Privacy Policy';
$tenant = currentTenant();
ob_start();
?>

<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-8 md:p-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Privacy Policy</h1>

        <div class="prose prose-gray max-w-none">
            <p class="text-gray-600 mb-4">Last updated: <?= date('F j, Y') ?></p>

            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">1. Information We Collect</h2>
            <p class="text-gray-600 mb-4">We collect information you provide directly to us, including:</p>
            <ul class="list-disc pl-6 text-gray-600 mb-4 space-y-2">
                <li>Name and email address when you sign up for our newsletter or download resources</li>
                <li>Account information when you create a customer account</li>
                <li>Order information including shipping and billing addresses</li>
                <li>Any other information you choose to provide</li>
            </ul>

            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">2. How We Use Your Information</h2>
            <p class="text-gray-600 mb-4">We use the information we collect to:</p>
            <ul class="list-disc pl-6 text-gray-600 mb-4 space-y-2">
                <li>Send you requested resources and downloads</li>
                <li>Process and fulfill your orders</li>
                <li>Send you marketing communications (with your consent)</li>
                <li>Improve our services and website</li>
            </ul>

            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">3. Data Storage</h2>
            <p class="text-gray-600 mb-4">Your data is stored securely on servers located in the EU. We implement appropriate technical and organizational measures to protect your personal data.</p>

            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">4. Your Rights</h2>
            <p class="text-gray-600 mb-4">Under GDPR, you have the right to:</p>
            <ul class="list-disc pl-6 text-gray-600 mb-4 space-y-2">
                <li>Access your personal data</li>
                <li>Rectify inaccurate personal data</li>
                <li>Request deletion of your personal data</li>
                <li>Object to processing of your personal data</li>
                <li>Data portability</li>
            </ul>

            <h2 class="text-xl font-semibold text-gray-900 mt-8 mb-4">5. Contact</h2>
            <p class="text-gray-600 mb-4">
                For questions about this privacy policy or your personal data, contact us at:
                <br><strong><?= h($tenant['email'] ?? 'info@kompaza.com') ?></strong>
            </p>

            <?php if ($tenant['company_name']): ?>
            <p class="text-gray-600 mb-4">
                <strong><?= h($tenant['company_name']) ?></strong><br>
                <?php if ($tenant['address']): ?><?= nl2br(h($tenant['address'])) ?><br><?php endif; ?>
                <?php if ($tenant['cvr_number']): ?>CVR: <?= h($tenant['cvr_number']) ?><?php endif; ?>
            </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/shop/layout.php';
?>
