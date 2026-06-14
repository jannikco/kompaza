<?php
$pageTitle = 'Prompt Library';
$metaDescription = 'Browse and copy AI prompts to supercharge your workflow.';
$tenant = $tenant ?? currentTenant();

$prompts = $prompts ?? [];
$categories = $categories ?? [];
$currentCategory = $currentCategory ?? '';
$search = $search ?? '';
$userTierLevel = $userTierLevel ?? 0;

ob_start();
?>

<section class="py-12 lg:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Page Header -->
        <div class="text-center mb-10">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Prompt Library</h1>
            <p class="mt-3 text-lg text-gray-500 max-w-2xl mx-auto">Ready-to-use AI prompts to help you get better results, faster.</p>
        </div>

        <!-- Search Bar -->
        <div class="max-w-xl mx-auto mb-8" x-data="{ q: '<?= h(addslashes($search)) ?>' }">
            <form action="/prompts" method="GET" class="relative">
                <?php if ($currentCategory): ?>
                    <input type="hidden" name="category" value="<?= h($currentCategory) ?>">
                <?php endif; ?>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="q" x-model="q" value="<?= h($search) ?>"
                           placeholder="Search prompts..."
                           class="w-full pl-12 pr-24 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 ring-brand focus:border-transparent bg-white shadow-sm">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                        <button type="submit" class="btn-brand px-4 py-1.5 text-white text-sm font-medium rounded-lg transition">
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Category Tabs -->
        <?php if (!empty($categories)): ?>
            <div class="mb-10 overflow-x-auto -mx-4 px-4 sm:mx-0 sm:px-0">
                <div class="flex items-center gap-2 min-w-max sm:flex-wrap sm:justify-center">
                    <a href="/prompts<?= $search ? '?q=' . urlencode($search) : '' ?>"
                       class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition whitespace-nowrap <?= !$currentCategory ? 'bg-brand text-white' : 'bg-white text-gray-600 border border-gray-200 hover:border-gray-300 hover:text-gray-900' ?>">
                        All
                    </a>
                    <?php foreach ($categories as $category): ?>
                        <a href="/prompts?category=<?= urlencode($category['slug']) ?><?= $search ? '&q=' . urlencode($search) : '' ?>"
                           class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition whitespace-nowrap <?= $currentCategory === $category['slug'] ? 'bg-brand text-white' : 'bg-white text-gray-600 border border-gray-200 hover:border-gray-300 hover:text-gray-900' ?>">
                            <?= h($category['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Active Filters -->
        <?php if ($search): ?>
            <div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
                <span>Showing results for</span>
                <span class="font-medium text-gray-900">"<?= h($search) ?>"</span>
                <a href="/prompts<?= $currentCategory ? '?category=' . urlencode($currentCategory) : '' ?>" class="text-brand hover:underline ml-1">Clear search</a>
            </div>
        <?php endif; ?>

        <!-- Prompt Cards -->
        <?php if (empty($prompts)): ?>
            <div class="text-center py-16">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                <p class="text-gray-500 text-lg">No prompts found.</p>
                <?php if ($search || $currentCategory): ?>
                    <p class="text-gray-400 text-sm mt-1">Try adjusting your search or category filter.</p>
                    <a href="/prompts" class="inline-flex items-center mt-4 text-sm text-brand hover:underline font-medium">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        View all prompts
                    </a>
                <?php else: ?>
                    <p class="text-gray-400 text-sm mt-1">Check back soon for new prompts.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <?php foreach ($prompts as $prompt): ?>
                    <?php
                        $hasAccess = $userTierLevel >= (int)($prompt['membership_tier_level'] ?? 0);
                        $tierLabel = 'Free';
                        $tierClasses = 'bg-green-100 text-green-700';
                        if ((int)($prompt['membership_tier_level'] ?? 0) === 2) {
                            $tierLabel = 'Pro';
                            $tierClasses = 'bg-blue-100 text-blue-700';
                        } elseif ((int)($prompt['membership_tier_level'] ?? 0) >= 3) {
                            $tierLabel = 'Premium';
                            $tierClasses = 'bg-purple-100 text-purple-700';
                        } elseif ((int)($prompt['membership_tier_level'] ?? 0) === 1) {
                            $tierLabel = 'Basic';
                            $tierClasses = 'bg-gray-100 text-gray-700';
                        }
                    ?>
                    <div class="bg-white rounded-xl border-2 overflow-hidden transition-shadow hover:shadow-md <?= !empty($prompt['is_featured']) ? 'border-brand' : 'border-gray-200' ?>"
                         x-data="{ copied: false, copying: false }">
                        <div class="p-6">
                            <!-- Header: Title + Badges -->
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <h3 class="text-base font-semibold text-gray-900 leading-snug"><?= h($prompt['title']) ?></h3>
                                <?php if (!empty($prompt['is_featured'])): ?>
                                    <span class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-yellow-100 text-yellow-800">
                                        <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        Featured
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Badges Row -->
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <?php if (!empty($prompt['category_name'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700"><?= h($prompt['category_name']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($prompt['ai_tool'])): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <?= h($prompt['ai_tool']) ?>
                                    </span>
                                <?php endif; ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $tierClasses ?>"><?= $tierLabel ?></span>
                            </div>

                            <!-- Description -->
                            <?php if (!empty($prompt['description'])): ?>
                                <p class="text-sm text-gray-500 leading-relaxed mb-4 <?= !$hasAccess ? 'line-clamp-2' : 'line-clamp-3' ?>"><?= h($prompt['description']) ?></p>
                            <?php endif; ?>

                            <!-- Prompt Preview (blurred if locked) -->
                            <?php if (!$hasAccess): ?>
                                <div class="relative mb-4">
                                    <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-500 leading-relaxed select-none" style="filter: blur(5px); -webkit-filter: blur(5px);">
                                        This is an example of what the prompt text looks like when you have access. Upgrade your membership to unlock this premium content and copy it directly.
                                    </div>
                                    <div class="absolute inset-0 flex items-center justify-center bg-gray-50/60 rounded-lg">
                                        <div class="text-center">
                                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            <p class="text-sm font-medium text-gray-700">Upgrade to access</p>
                                            <a href="/membership" class="inline-flex items-center mt-2 text-xs text-brand hover:underline font-medium">
                                                View plans
                                                <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Footer: Copy Count + Action -->
                            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                <div class="flex items-center text-xs text-gray-400">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    <?= number_format((int)($prompt['copy_count'] ?? 0)) ?> copies
                                </div>

                                <?php if ($hasAccess): ?>
                                    <button type="button"
                                            @click="
                                                if (copying) return;
                                                copying = true;
                                                fetch('/api/prompts/copy', {
                                                    method: 'POST',
                                                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                                    body: JSON.stringify({ id: <?= (int)$prompt['id'] ?> })
                                                })
                                                .then(r => r.json())
                                                .then(data => {
                                                    if (data.prompt_text) {
                                                        navigator.clipboard.writeText(data.prompt_text).then(() => {
                                                            copied = true;
                                                            copying = false;
                                                            setTimeout(() => copied = false, 2500);
                                                        }).catch(() => {
                                                            copying = false;
                                                            alert('Could not copy to clipboard. Please try again.');
                                                        });
                                                    } else {
                                                        copying = false;
                                                        alert(data.error || 'Could not copy prompt.');
                                                    }
                                                })
                                                .catch(() => { copying = false; alert('Network error. Please try again.'); });
                                            "
                                            :disabled="copying"
                                            class="inline-flex items-center px-4 py-2 btn-brand text-white text-sm font-medium rounded-lg transition disabled:opacity-50">
                                        <template x-if="!copied">
                                            <span class="flex items-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                Copy Prompt
                                            </span>
                                        </template>
                                        <template x-if="copied">
                                            <span class="flex items-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Copied!
                                            </span>
                                        </template>
                                    </button>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-400 text-sm font-medium rounded-lg cursor-not-allowed">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        Locked
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
