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

// --- Smart Color System (HSL-based) ---

// RGB→HSL conversion
$rgbToHsl = function($r, $g, $b) {
    $r /= 255; $g /= 255; $b /= 255;
    $max = max($r, $g, $b); $min = min($r, $g, $b);
    $h = $s = 0; $l = ($max + $min) / 2;
    if ($max !== $min) {
        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
        if ($max === $r) $h = (($g - $b) / $d + ($g < $b ? 6 : 0)) / 6;
        elseif ($max === $g) $h = (($b - $r) / $d + 2) / 6;
        else $h = (($r - $g) / $d + 4) / 6;
    }
    return [$h, $s, $l];
};

// HSL→Hex conversion
$hslToHex = function($h, $s, $l) {
    $h = fmod($h, 1.0); if ($h < 0) $h += 1;
    $s = max(0, min(1, $s)); $l = max(0, min(1, $l));
    if ($s == 0) { $v = (int)round($l * 255); return sprintf('#%02x%02x%02x', $v, $v, $v); }
    $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
    $p = 2 * $l - $q;
    $hue2rgb = function($p, $q, $t) {
        if ($t < 0) $t += 1; if ($t > 1) $t -= 1;
        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1/2) return $q;
        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
        return $p;
    };
    $rr = (int)round($hue2rgb($p, $q, $h + 1/3) * 255);
    $gg = (int)round($hue2rgb($p, $q, $h) * 255);
    $bb = (int)round($hue2rgb($p, $q, $h - 1/3) * 255);
    return sprintf('#%02x%02x%02x', $rr, $gg, $bb);
};

// HSL→RGB conversion (returns array)
$hslToRgb = function($h, $s, $l) {
    $h = fmod($h, 1.0); if ($h < 0) $h += 1;
    $s = max(0, min(1, $s)); $l = max(0, min(1, $l));
    if ($s == 0) { $v = (int)round($l * 255); return [$v, $v, $v]; }
    $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
    $p = 2 * $l - $q;
    $hue2rgb = function($p, $q, $t) {
        if ($t < 0) $t += 1; if ($t > 1) $t -= 1;
        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1/2) return $q;
        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
        return $p;
    };
    return [
        (int)round($hue2rgb($p, $q, $h + 1/3) * 255),
        (int)round($hue2rgb($p, $q, $h) * 255),
        (int)round($hue2rgb($p, $q, $h - 1/3) * 255),
    ];
};

// Brand color HSL
[$brandH, $brandS, $brandL] = $rgbToHsl($r, $g, $b);

// Luminance check (perceived brightness)
$luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
$isBrandDark = $luminance < 0.45;

// Text color that reads well ON the brand color
$textOnBrand = $isBrandDark ? '#ffffff' : '#1a1a2e';
$textOnBrandSoft = $isBrandDark ? 'rgba(255,255,255,0.8)' : 'rgba(26,26,46,0.7)';

// HSL-based gradient shades (replaces raw RGB ±40 for CTA sections)
$heroBgDarkerHsl  = $hslToHex($brandH, $brandS, max(0.08, $brandL - 0.12));
$heroBgLighterHsl = $hslToHex($brandH, $brandS, min(0.92, $brandL + 0.12));

// Mid-CTA safe gradient: guaranteed readable (lightness clamped 0.25–0.45)
$midCtaL = max(0.25, min(0.45, $brandL));
$midCtaBg = $hslToHex($brandH, $brandS, $midCtaL);
$midCtaBgDarker = $hslToHex($brandH, $brandS, max(0.15, $midCtaL - 0.10));

// Neon accent for Dark template: push lightness ≥0.55 so it pops on #0f172a
$neonL = $isBrandDark ? max(0.55, $brandL + 0.25) : $brandL;
$neonS = min(1.0, $brandS + 0.1); // slightly more saturated
$neonAccent = $hslToHex($brandH, $neonS, $neonL);
[$neonR, $neonG, $neonB] = $hslToRgb($brandH, $neonS, $neonL);

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
