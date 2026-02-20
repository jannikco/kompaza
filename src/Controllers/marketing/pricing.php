<?php

use App\Models\Plan;

$plans = Plan::allActive();

$pageTitle = 'Pricing - Kompaza';
$metaDescription = 'Simple, transparent pricing for every stage of growth. Start free for 7 days. Credit card required.';

ob_start();
include VIEWS_PATH . '/marketing/pricing.php';
$content = ob_get_clean();

include VIEWS_PATH . '/marketing/layout.php';
