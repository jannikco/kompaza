<!-- Verify Pending Section -->
<section class="min-h-screen bg-gray-50 py-12 lg:py-20">
    <div class="max-w-lg mx-auto px-4 sm:px-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 lg:p-10 text-center">
            <!-- Email icon -->
            <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-3">Check your email</h1>
            <p class="text-gray-600 mb-2">
                We've sent a verification link to
            </p>
            <?php if (!empty($email)): ?>
                <p class="text-indigo-600 font-semibold mb-6"><?= h($email) ?></p>
            <?php else: ?>
                <p class="text-gray-500 mb-6">your email address.</p>
            <?php endif; ?>

            <p class="text-sm text-gray-500 mb-6">
                Click the link in the email to verify your account and get started. The link expires in 24 hours.
            </p>

            <div class="bg-gray-50 rounded-lg p-4 text-left">
                <p class="text-sm text-gray-600 font-medium mb-2">Didn't receive the email?</p>
                <ul class="text-sm text-gray-500 space-y-1">
                    <li>- Check your spam or junk folder</li>
                    <li>- Make sure you entered the correct email</li>
                    <li>- <a href="/register" class="text-indigo-600 hover:text-indigo-700 font-medium">Try registering again</a></li>
                </ul>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-100">
                <a href="/login" class="text-sm text-gray-500 hover:text-gray-700">Back to login</a>
            </div>
        </div>
    </div>
</section>
