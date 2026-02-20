<?php
$pageTitle = 'Contact Us';
$tenant = currentTenant();
$metaDescription = 'Get in touch with us';

ob_start();
?>

<section class="py-16 lg:py-24">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900">Contact Us</h1>
            <p class="mt-3 text-gray-600">Have a question or feedback? We'd love to hear from you.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                    <?php foreach ($errors as $error): ?>
                        <li><?= h($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
            <form action="/contact" method="POST" class="space-y-5">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" required
                               value="<?= h($old['name'] ?? '') ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                               placeholder="Your name">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" required
                               value="<?= h($old['email'] ?? '') ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                               placeholder="you@example.com">
                    </div>
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <input type="text" id="subject" name="subject"
                           value="<?= h($old['subject'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                           placeholder="What is this about?">
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-red-500">*</span></label>
                    <textarea id="message" name="message" rows="6" required
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent"
                              placeholder="Your message..."><?= h($old['message'] ?? '') ?></textarea>
                </div>

                <button type="submit"
                        class="w-full btn-brand px-6 py-3 text-white font-semibold rounded-lg transition text-base">
                    Send Message
                </button>
            </form>
        </div>
    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
