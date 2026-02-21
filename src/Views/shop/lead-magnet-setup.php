<?php
/**
 * Shared setup for all lead magnet templates.
 * Expects $leadMagnet array from controller.
 */
$pageTitle = $leadMagnet['title'] ?? 'Free Download';
$tenant = currentTenant();
$metaDescription = $leadMagnet['meta_description'] ?? $leadMagnet['subtitle'] ?? '';
$heroBgColor = $leadMagnet['hero_bg_color'] ?? ($tenant['primary_color'] ?? '#1e40af');

// Compute gradient shades from hero bg color
$hexClean = ltrim($heroBgColor, '#');
if (strlen($hexClean) === 3) {
    $hexClean = $hexClean[0].$hexClean[0].$hexClean[1].$hexClean[1].$hexClean[2].$hexClean[2];
}
$r = hexdec(substr($hexClean, 0, 2));
$g = hexdec(substr($hexClean, 2, 2));
$b = hexdec(substr($hexClean, 4, 2));
$heroBgDarker  = sprintf('#%02x%02x%02x', max(0, $r - 40), max(0, $g - 40), max(0, $b - 40));
$heroBgLighter = sprintf('#%02x%02x%02x', min(255, $r + 40), min(255, $g + 40), min(255, $b + 40));

// Decode JSON content sections
$features = [];
if (!empty($leadMagnet['features'])) {
    $features = json_decode($leadMagnet['features'], true) ?: [];
}

$chapters = [];
if (!empty($leadMagnet['chapters'])) {
    $chapters = json_decode($leadMagnet['chapters'], true) ?: [];
}

$keyStatistics = [];
if (!empty($leadMagnet['key_statistics'])) {
    $keyStatistics = json_decode($leadMagnet['key_statistics'], true) ?: [];
}

$targetAudience = [];
if (!empty($leadMagnet['target_audience'])) {
    $targetAudience = json_decode($leadMagnet['target_audience'], true) ?: [];
}

$faqItems = [];
if (!empty($leadMagnet['faq'])) {
    $faqItems = json_decode($leadMagnet['faq'], true) ?: [];
}

$beforeAfter = null;
if (!empty($leadMagnet['before_after'])) {
    $beforeAfter = json_decode($leadMagnet['before_after'], true);
    if ($beforeAfter && empty($beforeAfter['before']) && empty($beforeAfter['after'])) {
        $beforeAfter = null;
    }
}

$testimonials = [];
if (!empty($leadMagnet['testimonial_templates'])) {
    $testimonials = json_decode($leadMagnet['testimonial_templates'], true) ?: [];
}

$socialProof = [];
if (!empty($leadMagnet['social_proof'])) {
    $socialProof = json_decode($leadMagnet['social_proof'], true) ?: [];
}

$authorBio = $leadMagnet['author_bio'] ?? '';

// Determine cover image: cover_image_path -> hero_image_path -> null
$coverImage = null;
if (!empty($leadMagnet['cover_image_path'])) {
    $coverImage = imageUrl($leadMagnet['cover_image_path']);
} elseif (!empty($leadMagnet['hero_image_path'])) {
    $coverImage = imageUrl($leadMagnet['hero_image_path']);
}

// Hero image (separate from cover - for split template etc.)
$heroImage = null;
if (!empty($leadMagnet['hero_image_path'])) {
    $heroImage = imageUrl($leadMagnet['hero_image_path']);
}

// Hero fields
$heroBadge = $leadMagnet['hero_badge'] ?? '';
$heroAccent = $leadMagnet['hero_headline_accent'] ?? '';
$heroHeadline = $leadMagnet['hero_headline'] ?? $leadMagnet['title'];

// Section headings helper — falls back to English defaults
$sectionHeadings = json_decode($leadMagnet['section_headings'] ?? '', true) ?: [];
$sh = fn($key, $default) => $sectionHeadings[$key] ?? $default;

// Brand-tinted section backgrounds
$bgLight = "background: rgba($r,$g,$b,0.02);";
$bgMedium = "background: rgba($r,$g,$b,0.05);";
