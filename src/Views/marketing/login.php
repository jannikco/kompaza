<!-- Login Section -->
<section class="min-h-screen bg-gray-50 py-12 lg:py-20 flex items-start justify-center">
    <div class="max-w-md w-full mx-auto px-4 sm:px-6">

        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <a href="/" class="inline-block mb-6">
                <img src="/images/kompaza-logo.svg" alt="Kompaza" class="h-16">
            </a>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Welcome back</h1>
            <p class="text-gray-500 text-sm">Find your workspace and log in</p>
        </div>

        <!-- Info box -->
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm text-blue-700 leading-relaxed">
                    Each Kompaza workspace has its own login page at <strong>your-company.kompaza.com</strong>. Use the form below to find your workspace and log in.
                </p>
            </div>
        </div>

        <!-- Workspace Finder -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8" x-data="loginForm()">

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <p class="ml-3 text-sm text-red-700"><?= h($error) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <form @submit.prevent="goToWorkspace" class="space-y-5">

                <!-- Subdomain Input -->
                <div>
                    <label for="workspace_slug" class="block text-sm font-medium text-gray-700 mb-1.5">Your workspace</label>
                    <div class="flex items-stretch">
                        <input type="text" id="workspace_slug" x-model="workspaceSlug" required
                               @input="workspaceSlug = workspaceSlug.toLowerCase().replace(/[^a-z0-9-]/g, '')"
                               placeholder="your-company"
                               class="flex-1 px-4 py-3 bg-gray-50 border border-gray-300 rounded-l-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                        <span class="inline-flex items-center px-4 py-3 bg-gray-100 border border-l-0 border-gray-300 rounded-r-lg text-gray-500 text-sm font-medium">
                            .kompaza.com
                        </span>
                    </div>
                </div>

                <!-- Go to workspace -->
                <button type="submit"
                        class="w-full px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition duration-200 shadow-sm shadow-indigo-600/25 text-sm">
                    Go to Workspace Login
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="px-3 bg-white text-sm text-gray-400">or</span>
                </div>
            </div>

            <!-- Direct login via POST (for users who know their workspace URL) -->
            <form method="POST" action="/login" class="space-y-5">
                <?= csrfField() ?>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                    <input type="email" id="email" name="email" required
                           value="<?= h($old['email'] ?? '') ?>"
                           placeholder="john@acme.com"
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                </div>

                <div>
                    <label for="slug_login" class="block text-sm font-medium text-gray-700 mb-1.5">Workspace slug</label>
                    <input type="text" id="slug_login" name="slug" required
                           value="<?= h($old['slug'] ?? '') ?>"
                           placeholder="your-company"
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-sm">
                    <p class="mt-1 text-xs text-gray-400">The subdomain of your Kompaza workspace</p>
                </div>

                <button type="submit"
                        class="w-full px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition duration-200 text-sm">
                    Redirect Me to My Workspace
                </button>
            </form>
        </div>

        <!-- Create account link -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                Don't have an account yet?
                <a href="/register" class="text-indigo-600 hover:text-indigo-700 font-medium">Sign up for free</a>
            </p>
        </div>
    </div>
</section>

<script>
function loginForm() {
    return {
        workspaceSlug: '',
        goToWorkspace() {
            if (this.workspaceSlug) {
                const protocol = window.location.protocol;
                window.location.href = protocol + '//' + this.workspaceSlug + '.<?= PLATFORM_DOMAIN ?>/login';
            }
        }
    }
}
</script>
