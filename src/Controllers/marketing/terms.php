<?php

$pageTitle = 'Terms of Service';
$metaDescription = 'Terms governing use of the Kompaza platform.';

ob_start();
include VIEWS_PATH . '/marketing/terms.php';
$content = ob_get_clean();

include VIEWS_PATH . '/marketing/layout.php';
