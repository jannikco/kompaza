<?php

use App\Models\Plan;

$pageTitle = 'Kompaza — Build Funnels, Sell Courses & Grow Your Audience';
$metaDescription = 'The all-in-one platform to build sales funnels and landing pages, sell online courses and memberships, run communities and webinars — with email automation and payments built in. A simpler, more complete ClickFunnels alternative.';

$plans = Plan::allActive();
$fromPrice = null;
foreach ($plans as $p) {
    $m = (int)($p['price_monthly_usd'] ?? 0);
    if ($m > 0 && ($fromPrice === null || $m < $fromPrice)) {
        $fromPrice = $m;
    }
}

ob_start();
include VIEWS_PATH . '/marketing/home.php';
$content = ob_get_clean();

include VIEWS_PATH . '/marketing/layout.php';
