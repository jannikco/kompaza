<?php
$pageTitle = $page['title'] ?? 'Page';
$tenant = currentTenant();
$metaDescription = $page['meta_description'] ?? '';
ob_start();
?>

<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?= $page['content'] ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/shop/layout.php';
?>
