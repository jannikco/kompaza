<!-- Registration Section -->
<section class="min-h-screen bg-gray-50 py-12 lg:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">

            <!-- Left: Benefits -->
            <div class="hidden lg:block">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">Start your 7-day free trial</h1>
                <p class="text-gray-600 text-lg mb-10 leading-relaxed">
                    Get instant access to all of Kompaza's features. Credit card required. Cancel anytime.
                </p>

                <div class="space-y-6">
                    <div class="flex items-start">
                        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-base font-semibold text-gray-900">Content Marketing Suite</h3>
                            <p class="text-sm text-gray-500 mt-1">Blog, e-books, and lead magnet landing pages out of the box.</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-base font-semibold text-gray-900">Customer &amp; Order Management</h3>
                            <p class="text-sm text-gray-500 mt-1">CRM, product catalog, and order processing with Stripe integration.</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="w-10 h-10 bg-cyan-100 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-base font-semibold text-gray-900">ConnectPilot LinkedIn Automation</h3>
                            <p class="text-sm text-gray-500 mt-1">Automated connection requests, message sequences, and lead tracking.</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-base font-semibold text-gray-900">Your Own Branded Storefront</h3>
                            <p class="text-sm text-gray-500 mt-1">Get your-company.kompaza.com instantly. Custom domain available on higher plans.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-10 p-6 bg-indigo-50 rounded-2xl border border-indigo-100">
                    <div class="flex items-center mb-3">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <?php endfor; ?>
                    </div>
                    <p class="text-sm text-indigo-900 leading-relaxed">
                        "We went from zero to a fully functional content marketing site in under an hour. The all-in-one approach saves us hundreds of dollars a month."
                    </p>
                    <p class="text-xs text-indigo-600 font-semibold mt-3">Morten K. &mdash; Marketing Consultant</p>
                </div>
            </div>

            <!-- Right: Registration Form -->
            <div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 lg:p-10">
                    <div class="lg:hidden mb-8">
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">Create your account</h1>
                        <p class="text-gray-500 text-sm">7-day free trial. Credit card required.</p>
                    </div>
                    <h2 class="hidden lg:block text-xl font-bold text-gray-900 mb-6">Create your account</h2>

                    <?php if (!empty($errors)): ?>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                <div class="ml-3">
                                    <?php foreach ($errors as $error): ?>
                                        <p class="text-sm text-red-700"><?= h($error) ?></p>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/register" x-data="registerForm()" class="space-y-5">
                        <?= csrfField() ?>
                        <?php if (!empty($selectedPlan)): ?>
                            <input type="hidden" name="plan" value="<?= h($selectedPlan) ?>">
                        <?php endif; ?>

                        <!-- Honeypot -->
                        <div style="position:absolute;left:-9999px" aria-hidden="true">
                            <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
                        </div>

                        <!-- Company Name -->
                        <div>
                            <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1.5">Company name</label>
                            <input type="text" id="company_name" name="company_name" required
                                   value="<?= h($old['company_name'] ?? '') ?>"
                                   placeholder="Acme Inc."
                                   class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                        </div>

                        <!-- Your Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Your name</label>
                            <input type="text" id="name" name="name" required
                                   value="<?= h($old['name'] ?? '') ?>"
                                   placeholder="John Doe"
                                   class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Work email</label>
                            <input type="email" id="email" name="email" required
                                   value="<?= h($old['email'] ?? '') ?>"
                                   placeholder="john@acme.com"
                                   class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                            <input type="password" id="password" name="password" required minlength="8"
                                   placeholder="Minimum 8 characters"
                                   class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                        </div>

                        <!-- Subdomain / Slug -->
                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 mb-1.5">Choose your subdomain</label>
                            <div class="flex items-stretch">
                                <input type="text" id="slug" name="slug" required
                                       x-model="slug"
                                       @input="slug = slug.toLowerCase().replace(/[^a-z0-9-]/g, '').replace(/--+/g, '-')"
                                       value="<?= h($old['slug'] ?? '') ?>"
                                       placeholder="your-company"
                                       pattern="[a-z0-9][a-z0-9\-]*[a-z0-9]"
                                       minlength="3" maxlength="50"
                                       class="flex-1 px-4 py-3 bg-gray-50 border border-gray-300 rounded-l-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                                <span class="inline-flex items-center px-4 py-3 bg-gray-100 border border-l-0 border-gray-300 rounded-r-lg text-gray-500 text-sm font-medium">
                                    .kompaza.com
                                </span>
                            </div>
                            <p class="mt-1.5 text-xs text-gray-400" x-show="slug">
                                Your site will be available at <span class="font-medium text-gray-600" x-text="slug + '.kompaza.com'"></span>
                            </p>
                        </div>

                        <!-- Submit -->
                        <div class="pt-2">
                            <button type="submit"
                                    class="w-full px-6 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition duration-200 shadow-sm shadow-indigo-600/25 hover:shadow-md text-sm">
                                Create Account &amp; Start Free Trial
                            </button>
                        </div>

                        <p class="text-xs text-gray-400 text-center leading-relaxed">
                            By creating an account, you agree to our
                            <a href="#" class="text-indigo-600 hover:text-indigo-700">Terms of Service</a> and
                            <a href="#" class="text-indigo-600 hover:text-indigo-700">Privacy Policy</a>.
                        </p>
                    </form>

                    <div class="mt-6 pt-6 border-t border-gray-100 text-center">
                        <p class="text-sm text-gray-500">
                            Already have an account?
                            <a href="/login" class="text-indigo-600 hover:text-indigo-700 font-medium">Log in</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function registerForm() {
    return {
        slug: '<?= h($old['slug'] ?? '') ?>',
    }
}
</script>
