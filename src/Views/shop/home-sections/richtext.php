<?php
/**
 * Homepage Section: Rich Text
 * Receives: $section, $tenant, $template
 */
$heading = $section['heading'] ?? '';
$body = $section['body'] ?? '';
if (empty($heading) && empty($body)) return;
?>
<section class="py-16 lg:py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (!empty($heading)): ?>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6 <?= $template === 'bold' ? 'text-center' : '' ?>"><?= h($heading) ?></h2>
        <?php endif; ?>
        <?php if (!empty($body)): ?>
            <div class="prose prose-lg max-w-none text-gray-600">
                <?= $body ?>
            </div>
        <?php endif; ?>
    </div>
</section>
