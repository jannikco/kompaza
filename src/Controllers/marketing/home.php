<?php

$pageTitle = 'Kompaza - All-in-One Platform for Content Marketing & Lead Generation';
$metaDescription = 'Create content, capture leads, manage customers, and automate LinkedIn outreach. All from one dashboard. Start your 7-day free trial.';

ob_start();
include VIEWS_PATH . '/marketing/home.php';
$content = ob_get_clean();

include VIEWS_PATH . '/marketing/layout.php';
