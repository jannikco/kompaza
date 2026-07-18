<?php

$pageTitle = 'Privacy Policy';
$metaDescription = 'How Kompaza collects, uses, and protects your data.';

ob_start();
include VIEWS_PATH . '/marketing/privacy.php';
$content = ob_get_clean();

include VIEWS_PATH . '/marketing/layout.php';
