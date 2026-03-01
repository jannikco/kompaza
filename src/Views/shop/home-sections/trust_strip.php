<?php
/**
 * Homepage Section: Trust Strip (bold template specific)
 * Receives: $section, $tenant, $template, $articles, $ebooks, $courses, $products
 */
$articleCount = count($articles ?? []);
$ebookCount = count($ebooks ?? []);
$courseCount = count($courses ?? []);
$productCount = count($products ?? []);
$totalContent = $articleCount + $ebookCount + $courseCount + $productCount;
if ($totalContent === 0) return;
?>
<section class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <?php if ($articleCount > 0): ?>
                <div>
                    <div class="text-3xl font-extrabold bold-gradient-text"><?= $articleCount ?>+</div>
                    <div class="text-sm text-gray-500 mt-1">Articles</div>
                </div>
            <?php endif; ?>
            <?php if ($ebookCount > 0): ?>
                <div>
                    <div class="text-3xl font-extrabold bold-gradient-text"><?= $ebookCount ?>+</div>
                    <div class="text-sm text-gray-500 mt-1">Ebooks</div>
                </div>
            <?php endif; ?>
            <?php if ($courseCount > 0): ?>
                <div>
                    <div class="text-3xl font-extrabold bold-gradient-text"><?= $courseCount ?>+</div>
                    <div class="text-sm text-gray-500 mt-1">Courses</div>
                </div>
            <?php endif; ?>
            <?php if ($productCount > 0): ?>
                <div>
                    <div class="text-3xl font-extrabold bold-gradient-text"><?= $productCount ?>+</div>
                    <div class="text-sm text-gray-500 mt-1">Products</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
