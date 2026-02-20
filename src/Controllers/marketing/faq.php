<?php

$pageTitle = 'FAQ - Kompaza';
$metaDescription = 'Find answers to frequently asked questions about Kompaza, pricing, features, ConnectPilot LinkedIn automation, and more.';

ob_start();
include VIEWS_PATH . '/marketing/faq.php';
$content = ob_get_clean();

include VIEWS_PATH . '/marketing/layout.php';
