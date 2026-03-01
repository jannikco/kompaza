<?php
/**
 * Homepage Section: Newsletter
 * Receives: $section, $tenant, $template
 */
$heading = $section['heading'] ?? 'Stay Updated';
$subtitle = $section['subtitle'] ?? 'Subscribe to our newsletter and never miss new content and offers.';
?>
<?php if ($template === 'bold'): ?>
<section class="py-16 lg:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bold-hero-gradient rounded-3xl p-8 sm:p-12 lg:p-16 text-center relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full mix-blend-overlay filter blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-80 h-80 bg-white rounded-full mix-blend-overlay filter blur-3xl"></div>
            </div>
            <div class="relative">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white mb-3"><?= h($heading) ?></h2>
                <p class="text-white/80 mb-8 text-lg max-w-xl mx-auto"><?= h($subtitle) ?></p>
                <form action="/newsletter-signup" method="POST" class="max-w-lg mx-auto" x-data="{ loading: false, done: false }"
                      @submit.prevent="
                          loading = true;
                          const fd = new FormData($el);
                          fetch('/newsletter-signup', { method: 'POST', body: fd })
                              .then(r => r.json())
                              .then(d => { loading = false; if (d.success) done = true; })
                              .catch(() => loading = false);
                      ">
                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
                    <div x-show="!done" class="flex flex-col sm:flex-row gap-3">
                        <input type="email" name="email" required placeholder="Your email address"
                               class="flex-1 px-5 py-4 border-0 rounded-xl text-sm focus:outline-none focus:ring-2 ring-white/50 shadow-lg">
                        <button type="submit" :disabled="loading"
                                class="px-8 py-4 bg-white text-gray-900 font-bold rounded-xl hover:bg-gray-100 transition shadow-lg text-sm whitespace-nowrap disabled:opacity-50">
                            <span x-show="!loading">Subscribe</span>
                            <span x-show="loading" x-cloak>Sending...</span>
                        </button>
                    </div>
                    <div x-show="done" x-cloak class="text-white font-semibold py-4 text-lg">
                        Thank you for subscribing!
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<?php elseif ($template === 'elegant'): ?>
<section class="py-16 lg:py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3"><?= h($heading) ?></h2>
            <p class="text-gray-500 mb-8 leading-relaxed"><?= h($subtitle) ?></p>
            <form action="/newsletter-signup" method="POST" class="max-w-md mx-auto" x-data="{ loading: false, done: false }"
                  @submit.prevent="
                      loading = true;
                      const fd = new FormData($el);
                      fetch('/newsletter-signup', { method: 'POST', body: fd })
                          .then(r => r.json())
                          .then(d => { loading = false; if (d.success) done = true; })
                          .catch(() => loading = false);
                  ">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
                <div x-show="!done" class="flex flex-col sm:flex-row gap-3">
                    <input type="email" name="email" required placeholder="Your email address"
                           class="flex-1 px-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent bg-white">
                    <button type="submit" :disabled="loading"
                            class="btn-brand px-6 py-3 text-white font-semibold rounded-lg transition text-sm whitespace-nowrap disabled:opacity-50">
                        <span x-show="!loading">Subscribe</span>
                        <span x-show="loading" x-cloak>Sending...</span>
                    </button>
                </div>
                <div x-show="done" x-cloak class="text-green-600 font-medium py-3">
                    Thank you for subscribing!
                </div>
            </form>
            <p class="mt-4 text-xs text-gray-400">No spam, unsubscribe at any time.</p>
        </div>
    </div>
</section>
<?php else: /* starter */ ?>
<section class="py-16 lg:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl border border-gray-200 p-8 sm:p-12 text-center max-w-2xl mx-auto">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3"><?= h($heading) ?></h2>
            <p class="text-gray-500 mb-8"><?= h($subtitle) ?></p>
            <form action="/newsletter-signup" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto" x-data="{ loading: false, done: false }"
                  @submit.prevent="
                      loading = true;
                      const fd = new FormData($el);
                      fetch('/newsletter-signup', { method: 'POST', body: fd })
                          .then(r => r.json())
                          .then(d => { loading = false; if (d.success) done = true; })
                          .catch(() => loading = false);
                  ">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCsrfToken() ?>">
                <div x-show="!done" class="flex flex-col sm:flex-row gap-3 w-full">
                    <input type="email" name="email" required placeholder="Your email address"
                           class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent">
                    <button type="submit" :disabled="loading"
                            class="btn-brand px-6 py-3 text-white font-semibold rounded-lg transition text-sm whitespace-nowrap disabled:opacity-50">
                        <span x-show="!loading">Subscribe</span>
                        <span x-show="loading" x-cloak>Sending...</span>
                    </button>
                </div>
                <div x-show="done" x-cloak class="text-green-600 font-medium py-3">
                    Thank you for subscribing!
                </div>
            </form>
        </div>
    </div>
</section>
<?php endif; ?>
