<?php
$pageTitle = $article['title'] ?? 'Article';
$tenant = currentTenant();
$metaDescription = $article['meta_description'] ?? $article['excerpt'] ?? '';

// $article should be passed from the controller
// $relatedArticles should be passed from the controller (optional)
$relatedArticles = $relatedArticles ?? [];

ob_start();
?>

<article class="py-12 lg:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="mb-8">
                <ol class="flex items-center text-sm text-gray-400 space-x-2">
                    <li><a href="/" class="hover:text-gray-600 transition">Home</a></li>
                    <li><span>/</span></li>
                    <li><a href="/blog" class="hover:text-gray-600 transition">Blog</a></li>
                    <li><span>/</span></li>
                    <li class="text-gray-600 truncate max-w-xs"><?= h($article['title']) ?></li>
                </ol>
            </nav>

            <!-- Article Header -->
            <header class="mb-8">
                <?php if (!empty($article['category'])): ?>
                    <span class="inline-block text-xs font-semibold text-brand uppercase tracking-wider mb-3"><?= h($article['category']) ?></span>
                <?php endif; ?>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 leading-tight"><?= h($article['title']) ?></h1>
                <div class="flex items-center gap-4 mt-6 text-sm text-gray-500">
                    <?php if (!empty($article['author_name'])): ?>
                        <span>By <strong class="text-gray-700"><?= h($article['author_name']) ?></strong></span>
                        <span class="text-gray-300">|</span>
                    <?php endif; ?>
                    <time datetime="<?= h($article['published_at'] ?? $article['created_at']) ?>">
                        <?= formatDate($article['published_at'] ?? $article['created_at'], 'd M Y') ?>
                    </time>
                    <?php if (!empty($article['view_count'])): ?>
                        <span class="text-gray-300">|</span>
                        <span><?= number_format($article['view_count']) ?> views</span>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Featured Image -->
            <?php if (!empty($article['featured_image'])): ?>
                <div class="mb-10 rounded-xl overflow-hidden border border-gray-200">
                    <img src="<?= h(imageUrl($article['featured_image'])) ?>" alt="<?= h($article['title']) ?>"
                         class="w-full h-auto object-cover">
                </div>
            <?php endif; ?>

            <!-- Article Content -->
            <div class="prose prose-lg prose-gray max-w-none
                         prose-headings:text-gray-900 prose-headings:font-bold
                         prose-a:text-brand prose-a:no-underline hover:prose-a:underline
                         prose-img:rounded-xl prose-img:border prose-img:border-gray-200
                         prose-pre:bg-gray-900 prose-pre:text-gray-100
                         prose-blockquote:border-l-brand">
                <?= $article['content'] ?? '' ?>
            </div>

            <!-- Tags -->
            <?php if (!empty($article['tags'])): ?>
                <div class="mt-10 pt-6 border-t border-gray-200">
                    <div class="flex flex-wrap gap-2">
                        <?php foreach (explode(',', $article['tags']) as $tag): ?>
                            <span class="inline-block px-3 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">
                                <?= h(trim($tag)) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Share -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-sm font-medium text-gray-500 mb-3">Share this article</p>
                <div class="flex gap-3">
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(url('blog/' . $article['slug'])) ?>"
                       target="_blank" rel="noopener"
                       class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-600 text-sm font-medium transition">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        LinkedIn
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode(url('blog/' . $article['slug'])) ?>&text=<?= urlencode($article['title']) ?>"
                       target="_blank" rel="noopener"
                       class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-600 text-sm font-medium transition">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        Twitter
                    </a>
                </div>
            </div>
        </div>

        <!-- Related Articles -->
        <?php if (!empty($relatedArticles)): ?>
            <div class="max-w-7xl mx-auto mt-16 pt-12 border-t border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900 mb-8">Related Articles</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($relatedArticles as $related): ?>
                        <a href="/blog/<?= h($related['slug']) ?>" class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                            <?php if (!empty($related['featured_image'])): ?>
                                <div class="aspect-video overflow-hidden">
                                    <img src="<?= h(imageUrl($related['featured_image'])) ?>" alt="<?= h($related['title']) ?>"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>
                            <?php else: ?>
                                <div class="aspect-video bg-gray-100 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                </div>
                            <?php endif; ?>
                            <div class="p-5">
                                <h3 class="font-semibold text-gray-900 group-hover:text-brand transition line-clamp-2"><?= h($related['title']) ?></h3>
                                <p class="mt-2 text-xs text-gray-400"><?= formatDate($related['published_at'] ?? $related['created_at']) ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</article>

<?php $content = ob_get_clean(); include VIEWS_PATH . '/shop/layout.php'; ?>
