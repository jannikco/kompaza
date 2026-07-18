<?php
/**
 * Multi-currency catalog for the jannikhansen first-tenant import.
 * Amounts are major units (DKK/EUR/GBP/USD).
 */

function jhTrackPrices(): array {
    return [
        'office-os' => [
            'full' => ['dkk' => 2500, 'eur' => 329, 'gbp' => 279, 'usd' => 349],
            'plan' => ['dkk' => 899,  'eur' => 119, 'gbp' => 99,  'usd' => 129], // ×3 installments
            'name' => 'Office OS',
            'name_en' => 'Office OS',
        ],
        'creator-os' => [
            'full' => ['dkk' => 9997, 'eur' => 1295, 'gbp' => 1095, 'usd' => 1495],
            'plan' => ['dkk' => 3499, 'eur' => 449,  'gbp' => 379,  'usd' => 519],
            'name' => 'Creator OS',
            'name_en' => 'Creator OS',
        ],
        'founder-os' => [
            'full' => ['dkk' => 14997, 'eur' => 1995, 'gbp' => 1695, 'usd' => 2195],
            'plan' => ['dkk' => 5199, 'eur' => 699,  'gbp' => 589,  'usd' => 759],
            'name' => 'Founder OS',
            'name_en' => 'Founder OS',
        ],
    ];
}

function jhLibraryTier(): array {
    return [
        'free'     => ['dkk' => 0,    'eur' => 0,   'gbp' => 0,   'usd' => 0],
        'single'   => ['dkk' => 295,  'eur' => 39,  'gbp' => 35,  'usd' => 45],
        'flagship' => ['dkk' => 495,  'eur' => 65,  'gbp' => 59,  'usd' => 75],
        'toolkit'  => ['dkk' => 795,  'eur' => 105, 'gbp' => 95,  'usd' => 115],
        'bundle'   => ['dkk' => 2495, 'eur' => 329, 'gbp' => 299, 'usd' => 365],
    ];
}

/**
 * Resolve visitor currency: cookie > CF country > default DKK for DK, EUR else.
 * Named visitorCurrency to avoid clashing with other apps' helpers.
 */
function visitorCurrency(): string {
    $allowed = ['dkk', 'eur', 'gbp', 'usd'];
    $cookie = strtolower($_COOKIE['kz_cur'] ?? $_COOKIE['cur_pref'] ?? '');
    if (in_array($cookie, $allowed, true)) {
        return $cookie;
    }
    // Explicit query sticky
    if (!empty($_GET['cur']) && in_array(strtolower($_GET['cur']), $allowed, true)) {
        $cur = strtolower($_GET['cur']);
        setcookie('kz_cur', $cur, time() + 365 * 86400, '/', '', true, false);
        return $cur;
    }
    $cc = strtoupper($_SERVER['HTTP_CF_IPCOUNTRY'] ?? $_SERVER['HTTP_X_COUNTRY_CODE'] ?? '');
    if ($cc === 'DK') return 'dkk';
    if (in_array($cc, ['GB', 'UK'], true)) return 'gbp';
    if (in_array($cc, ['US', 'CA', 'AU', 'NZ'], true)) return 'usd';
    if ($cc !== '' && $cc !== 'XX') return 'eur';
    return 'dkk';
}

if (!function_exists('currentCurrency')) {
    function currentCurrency(): string {
        return visitorCurrency();
    }
}

function jhTrackAmount(string $slug, string $sku = 'full', ?string $currency = null): float {
    $currency = $currency ?: visitorCurrency();
    $tracks = jhTrackPrices();
    if (!isset($tracks[$slug])) return 0;
    $sku = $sku === 'buy-plan' ? 'plan' : $sku;
    if ($sku === 'plan') {
        return (float)($tracks[$slug]['plan'][$currency] ?? $tracks[$slug]['plan']['dkk']);
    }
    return (float)($tracks[$slug]['full'][$currency] ?? $tracks[$slug]['full']['dkk']);
}
