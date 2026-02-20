<?php

$pageTitle = 'About Us';
$metaDescription = 'Kompaza — Where creators turn knowledge into gold. We help serious creators build digital assets, not just upload content.';

ob_start();
include VIEWS_PATH . '/marketing/about.php';
$content = ob_get_clean();

include VIEWS_PATH . '/marketing/layout.php';
