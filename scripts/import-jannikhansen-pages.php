<?php
/**
 * Import live jannikhansen.com marketing pages into Kompaza as full custom_pages.
 *
 * Usage (on server or locally with DB access):
 *   php scripts/import-jannikhansen-pages.php [--dry-run] [--locale=da|en|both]
 *
 * Prerequisites:
 *   - Tenant slug `jannikhansen` exists
 *   - Assets copied to public/uploads/{tenant_id}/img/ (see import-jannikhansen-assets.sh)
 */

$dryRun = in_array('--dry-run', $argv, true);
$locale = 'da';
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--locale=')) {
        $locale = substr($arg, 9);
    }
}

$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    die("ERROR: .env not found\n");
}
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
    [$k, $v] = explode('=', $line, 2);
    $_ENV[trim($k)] = trim($v, " \t\"'");
}

$pdo = new PDO(
    sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $_ENV['DB_HOST'] ?? 'localhost',
        $_ENV['DB_PORT'] ?? '3306',
        $_ENV['DB_DATABASE'] ?? 'kompaza'
    ),
    $_ENV['DB_USERNAME'] ?? 'root',
    $_ENV['DB_PASSWORD'] ?? '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

$stmt = $pdo->prepare("SELECT id FROM tenants WHERE slug = 'jannikhansen'");
$stmt->execute();
$tenant = $stmt->fetch();
if (!$tenant) {
    die("ERROR: Tenant jannikhansen not found. Run migration 032 first.\n");
}
$tenantId = (int)$tenant['id'];
echo "Tenant ID: $tenantId\n";
if ($dryRun) echo "=== DRY RUN ===\n";

// Phase 0 pages — DA-first; EN variants get en- prefix slugs
$daPages = [
    ['url' => '/',            'slug' => 'homepage',   'title' => 'Jannik Hansen',              'is_homepage' => true,  'sort_order' => 0],
    ['url' => '/tracks',      'slug' => 'tracks',     'title' => 'Find your track',            'is_homepage' => false, 'sort_order' => 1],
    ['url' => '/creator-os',  'slug' => 'creator-os', 'title' => 'Creator OS',                 'is_homepage' => false, 'sort_order' => 2],
    ['url' => '/office-os',   'slug' => 'office-os',  'title' => 'Office OS',                  'is_homepage' => false, 'sort_order' => 3],
    ['url' => '/founder-os',  'slug' => 'founder-os', 'title' => 'Founder OS',                 'is_homepage' => false, 'sort_order' => 4],
    ['url' => '/bibliotek',   'slug' => 'bibliotek',  'title' => 'The Library',                'is_homepage' => false, 'sort_order' => 5],
    ['url' => '/about',       'slug' => 'about',      'title' => 'About',                      'is_homepage' => false, 'sort_order' => 6],
    ['url' => '/faq',         'slug' => 'faq',        'title' => 'FAQ',                        'is_homepage' => false, 'sort_order' => 7],
    ['url' => '/contact',     'slug' => 'contact',    'title' => 'Contact',                    'is_homepage' => false, 'sort_order' => 8],
    ['url' => '/workshop',    'slug' => 'workshop',   'title' => 'Free Workshop',              'is_homepage' => false, 'sort_order' => 9],
    ['url' => '/privacy',     'slug' => 'privacy',    'title' => 'Privacy Policy',             'is_homepage' => false, 'sort_order' => 90],
    ['url' => '/terms',       'slug' => 'terms',      'title' => 'Terms of Service',           'is_homepage' => false, 'sort_order' => 91],
];

$enPages = [
    ['url' => '/en',              'slug' => 'en-home',       'title' => 'Jannik Hansen (EN)',     'is_homepage' => false, 'sort_order' => 100],
    ['url' => '/en/tracks',       'slug' => 'en-tracks',     'title' => 'Find your track (EN)',   'is_homepage' => false, 'sort_order' => 101],
    ['url' => '/en/creator-os',   'slug' => 'en-creator-os', 'title' => 'Creator OS (EN)',        'is_homepage' => false, 'sort_order' => 102],
    ['url' => '/en/library',      'slug' => 'en-library',    'title' => 'The Library (EN)',       'is_homepage' => false, 'sort_order' => 103],
    ['url' => '/en/about',        'slug' => 'en-about',      'title' => 'About (EN)',             'is_homepage' => false, 'sort_order' => 104],
];

$pages = match ($locale) {
    'en' => $enPages,
    'both' => array_merge($daPages, $enPages),
    default => $daPages,
};

function fetchJhPage(string $path, string $cookieLocale = 'da'): string|false
{
    $url = 'https://jannikhansen.com' . $path;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 45,
        CURLOPT_USERAGENT => 'Kompaza-JH-Import/1.0',
        CURLOPT_HTTPHEADER => [
            'Cookie: loc_pref=' . $cookieLocale . '; cur_pref=dkk',
            'Accept-Language: ' . ($cookieLocale === 'da' ? 'da,en;q=0.8' : 'en,da;q=0.8'),
        ],
    ]);
    $html = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($html === false || $code !== 200) {
        echo "  ERROR HTTP $code $err for $path\n";
        return false;
    }
    return $html;
}

function transformJhHtml(string $html, int $tenantId): string
{
    $uploadBase = "/uploads/{$tenantId}/img";

    // Image / asset paths
    $html = str_replace(['src="/img/', "src='/img/"], ["src=\"{$uploadBase}/", "src='{$uploadBase}/"], $html);
    $html = str_replace(['url(/img/', 'url("/img/', "url('/img/"], ["url({$uploadBase}/", "url(\"{$uploadBase}/", "url('{$uploadBase}/"], $html);
    $html = str_replace(['content="/img/', 'href="/img/'], ["content=\"{$uploadBase}/", "href=\"{$uploadBase}/"], $html);

    // Absolute jannikhansen URLs → relative
    $html = preg_replace('#https?://(www\.)?jannikhansen\.com#i', '', $html);

    // Map marketing paths that stay as custom pages
    $map = [
        '/en/creator-os' => '/en-creator-os',
        '/en/tracks' => '/en-tracks',
        '/en/library' => '/en-library',
        '/en/about' => '/en-about',
        '/en/office-os' => '/office-os',
        '/en/founder-os' => '/founder-os',
        '/en' => '/en-home',
        '/bibliotek' => '/bibliotek',
        '/library' => '/bibliotek',
        '/creator-os' => '/creator-os',
        '/office-os' => '/office-os',
        '/founder-os' => '/founder-os',
        '/tracks' => '/tracks',
        '/workshop' => '/workshop',
        '/about' => '/about',
        '/faq' => '/faq',
        '/contact' => '/contact',
        '/privacy' => '/privacy',
        '/terms' => '/terms',
    ];
    // Longest first
    uksort($map, fn($a, $b) => strlen($b) <=> strlen($a));
    foreach ($map as $from => $to) {
        $html = str_replace('href="' . $from . '"', 'href="' . $to . '"', $html);
        $html = str_replace("href='" . $from . "'", "href='" . $to . "'", $html);
        $html = str_replace('href="' . $from . '/', 'href="' . $to . '/', $html);
    }

    // Point commerce-ish CTAs toward native Kompaza routes where useful
    $html = str_replace('href="/courses"', 'href="/kurser"', $html);
    $html = str_replace('href="/modules"', 'href="/kurser"', $html);

    // Strip tracking beacons that will 404
    $html = preg_replace('#<img[^>]+/email/o\.gif[^>]*>#i', '', $html);

    // Ensure homepage slug "/" internal self-links work
    $html = preg_replace('#href="/\?(ver|lang|cur)=[^"]*"#', 'href="/"', $html);

    return $html;
}

function upsertPage(PDO $pdo, int $tenantId, array $page, string $html, bool $dryRun): void
{
    $check = $pdo->prepare("SELECT id FROM custom_pages WHERE tenant_id = ? AND slug = ?");
    $check->execute([$tenantId, $page['slug']]);
    $existing = $check->fetch();

    $meta = null;
    if (preg_match('/<meta\s+name=["\']description["\']\s+content=["\']([^"\']+)["\']/i', $html, $m)) {
        $meta = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    if ($dryRun) {
        echo "  would " . ($existing ? 'UPDATE' : 'INSERT') . " {$page['slug']} (" . strlen($html) . " bytes)\n";
        return;
    }

    if ($page['is_homepage']) {
        $pdo->prepare("UPDATE custom_pages SET is_homepage = 0 WHERE tenant_id = ?")->execute([$tenantId]);
    }

    if ($existing) {
        $stmt = $pdo->prepare("
            UPDATE custom_pages
            SET title = ?, content = ?, layout = 'full', meta_description = ?, status = 'published',
                is_homepage = ?, sort_order = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $page['title'],
            $html,
            $meta,
            $page['is_homepage'] ? 1 : 0,
            $page['sort_order'],
            $existing['id'],
        ]);
        echo "  UPDATED {$page['slug']}\n";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO custom_pages (tenant_id, slug, title, content, layout, meta_description, status, is_homepage, sort_order, created_at)
            VALUES (?, ?, ?, ?, 'full', ?, 'published', ?, ?, NOW())
        ");
        $stmt->execute([
            $tenantId,
            $page['slug'],
            $page['title'],
            $html,
            $meta,
            $page['is_homepage'] ? 1 : 0,
            $page['sort_order'],
        ]);
        echo "  INSERTED {$page['slug']}\n";
    }
}

$ok = 0;
$fail = 0;
foreach ($pages as $page) {
    echo "Fetch {$page['url']} → /{$page['slug']}\n";
    $cookieLoc = str_starts_with($page['url'], '/en') ? 'en' : 'da';
    $html = fetchJhPage($page['url'], $cookieLoc);
    if ($html === false) {
        $fail++;
        continue;
    }
    $html = transformJhHtml($html, $tenantId);
    upsertPage($pdo, $tenantId, $page, $html, $dryRun);
    $ok++;
}

echo "\nDone. ok=$ok fail=$fail\n";
echo "Visit: https://jannikhansen.kompaza.com/\n";
