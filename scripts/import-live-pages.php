#!/usr/bin/env php
<?php
/**
 * Import Live AIbootcamphq.com Pages to Kompaza
 *
 * Fetches rendered HTML from the LIVE aibootcamphq.com site,
 * transforms links/images/forms, and upserts as custom_pages
 * for the aibootcamphq tenant in Kompaza.
 *
 * Prerequisites:
 *   - Images already copied: cp /var/www/html/aibootcamphq.com/public/img/* /var/www/kompaza.com/public/uploads/3/img/
 *
 * Usage: php scripts/import-live-pages.php [--dry-run]
 */

$dryRun = in_array('--dry-run', $argv);

// Load .env from project root
$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    die("ERROR: .env file not found at $envFile\n");
}
$envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($envLines as $line) {
    if (str_starts_with(trim($line), '#')) continue;
    if (str_contains($line, '=')) {
        [$key, $val] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($val);
    }
}

$kompazaDb = new PDO(
    sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $_ENV['DB_HOST'] ?? 'localhost',
        $_ENV['DB_PORT'] ?? '3306',
        $_ENV['DB_DATABASE'] ?? 'kompaza'
    ),
    $_ENV['DB_USERNAME'] ?? 'root',
    $_ENV['DB_PASSWORD'] ?? '',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

// Get tenant ID
$stmt = $kompazaDb->prepare("SELECT id FROM tenants WHERE slug = 'aibootcamphq'");
$stmt->execute();
$tenant = $stmt->fetch();
if (!$tenant) {
    die("ERROR: Tenant 'aibootcamphq' not found.\n");
}
$tenantId = $tenant['id'];
echo "Tenant ID: $tenantId\n";

if ($dryRun) {
    echo "=== DRY RUN MODE ===\n\n";
}

// Pages to import from live site
$pages = [
    // Main pages
    ['url' => '/dk',                      'slug' => 'homepage',              'title' => 'AI BootCamp HQ',                'is_homepage' => true,  'sort_order' => 0],
    ['url' => '/dk/gratis',               'slug' => 'gratis',               'title' => 'Gratis PDF\'er & AI Guides',     'is_homepage' => false, 'sort_order' => 1],
    ['url' => '/dk/foredrag',             'slug' => 'foredrag',             'title' => 'Foredrag om AI',                 'is_homepage' => false, 'sort_order' => 2],
    ['url' => '/dk/konsulent',            'slug' => 'konsulent',            'title' => 'AI Konsulent',                   'is_homepage' => false, 'sort_order' => 3],
    ['url' => '/dk/ai-konsulent',         'slug' => 'ai-konsulent',         'title' => 'AI Konsulenterne',               'is_homepage' => false, 'sort_order' => 4],
    ['url' => '/dk/claude-cowork-kursus', 'slug' => 'claude-cowork-kursus', 'title' => 'Claude Cowork Masterclass',      'is_homepage' => false, 'sort_order' => 5],
    ['url' => '/dk/hjemmeside',           'slug' => 'hjemmeside',           'title' => 'Byg hjemmeside med AI',          'is_homepage' => false, 'sort_order' => 6],
    ['url' => '/dk/lp-klar-til-ai',       'slug' => 'lp-klar-til-ai',      'title' => 'AI Klar Til Kamp',               'is_homepage' => false, 'sort_order' => 7],
    // Ebook landing pages
    ['url' => '/dk/ebook-landing',        'slug' => 'ebook-landing',        'title' => 'LinkedIn AI Mastery',            'is_homepage' => false, 'sort_order' => 10],
    ['url' => '/dk/mba',                  'slug' => 'mba',                  'title' => 'MBA i AI',                       'is_homepage' => false, 'sort_order' => 11],
    // Free lead magnet pages
    ['url' => '/dk/free-ai-prompts',      'slug' => 'free-ai-prompts',      'title' => 'Gratis AI Prompts',              'is_homepage' => false, 'sort_order' => 20],
    ['url' => '/dk/free-atlas',           'slug' => 'free-atlas',           'title' => 'Gratis Atlas Guide',             'is_homepage' => false, 'sort_order' => 21],
    ['url' => '/dk/free-udlaeg',          'slug' => 'free-udlaeg',          'title' => 'Gratis AI Kvitteringer Guide',   'is_homepage' => false, 'sort_order' => 22],
    ['url' => '/dk/free-konsulent-ai-tools', 'slug' => 'free-konsulent-ai-tools', 'title' => 'Gratis AI Konsulent Tools', 'is_homepage' => false, 'sort_order' => 23],
    ['url' => '/dk/free-claude-cowork',   'slug' => 'free-claude-cowork',   'title' => 'Gratis Claude Cowork Kapitel',   'is_homepage' => false, 'sort_order' => 24],
    ['url' => '/dk/free-gdpr-tjekliste',  'slug' => 'free-gdpr-tjekliste',  'title' => 'Gratis GDPR Tjekliste',         'is_homepage' => false, 'sort_order' => 25],
    // Purchase / course pages
    ['url' => '/dk/course-purchase',      'slug' => 'course-purchase',      'title' => 'Køb Kursus',                    'is_homepage' => false, 'sort_order' => 30],
    ['url' => '/dk/book-purchase',        'slug' => 'book-purchase',        'title' => 'Køb Ebog',                      'is_homepage' => false, 'sort_order' => 31],
    ['url' => '/dk/claude-cowork-kursus-koeb', 'slug' => 'claude-cowork-kursus-koeb', 'title' => 'Køb Claude Cowork Kursus', 'is_homepage' => false, 'sort_order' => 32],
    // Legal
    ['url' => '/dk/privacy-policy',       'slug' => 'privacy',              'title' => 'Privatlivspolitik',              'is_homepage' => false, 'sort_order' => 90],
];

/**
 * Fetch page HTML via cURL
 */
function fetchPage(string $url): string|false
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://aibootcamphq.com{$url}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'Kompaza-Import/1.0',
    ]);
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($html === false || $httpCode !== 200) {
        echo "  ERROR: HTTP $httpCode - $error\n";
        return false;
    }

    return $html;
}

/**
 * Transform HTML for Kompaza hosting
 */
function transformHtml(string $html): string
{
    // 1. Replace image paths: /img/ → /uploads/3/img/
    $html = str_replace('src="/img/', 'src="/uploads/3/img/', $html);
    $html = str_replace("src='/img/", "src='/uploads/3/img/", $html);
    // Also in CSS background-image, og:image meta tags, etc.
    $html = str_replace('url(/img/', 'url(/uploads/3/img/', $html);
    $html = str_replace('url("/img/', 'url("/uploads/3/img/', $html);
    $html = str_replace("url('/img/", "url('/uploads/3/img/", $html);
    $html = str_replace('content="/img/', 'content="/uploads/3/img/', $html);
    $html = str_replace('href="/img/', 'href="/uploads/3/img/', $html);

    // 2. Remove Matomo analytics
    $html = preg_replace('/<!-- Matomo -->.*?<!-- End Matomo Code -->/s', '', $html);
    $html = preg_replace('/<!-- Matomo -->\s*<script>.*?<\/script>/s', '', $html);
    // Remove Matomo tag manager
    $html = preg_replace('/<!-- Matomo Tag Manager -->.*?<!-- End Matomo Tag Manager -->/s', '', $html);
    // Remove standalone matomo/analytics script blocks
    $html = preg_replace('/<script[^>]*>.*?mtm\.push.*?<\/script>/s', '', $html);
    $html = preg_replace('/<script[^>]*>.*?_paq\.push.*?<\/script>/s', '', $html);
    // Remove matomo noscript tags
    $html = preg_replace('/<noscript>.*?matomo.*?<\/noscript>/si', '', $html);

    // 3. Remove Brevo chat widget
    $html = preg_replace('/<!-- Brevo Conversations.*?<\/script>/s', '', $html);
    $html = preg_replace('/<script[^>]*>.*?BrevoConversations.*?<\/script>/s', '', $html);

    // 4. Rewrite internal navigation links
    // Order matters - most specific first, then general patterns
    $linkReplacements = [
        // Specific /dk/ paths → Kompaza paths
        'href="/dk/gratis"'               => 'href="/gratis"',
        'href="/dk/foredrag"'             => 'href="/foredrag"',
        'href="/dk/konsulent"'            => 'href="/konsulent"',
        'href="/dk/ai-konsulent"'         => 'href="/ai-konsulent"',
        'href="/dk/eboeger"'              => 'href="/eboger"',
        'href="/dk/hjemmeside"'           => 'href="/hjemmeside"',
        'href="/dk/claude-cowork-kursus"' => 'href="/claude-cowork-kursus"',
        'href="/dk/lp-klar-til-ai"'       => 'href="/lp-klar-til-ai"',
        'href="/dk/privacy-policy"'       => 'href="/privacy"',
        'href="/dk/terms-of-service"'     => 'href="/terms"',
        'href="/dk/"'                     => 'href="/"',
        'href="/dk"'                      => 'href="/"',

        // Single-quoted variants
        "href='/dk/gratis'"               => "href='/gratis'",
        "href='/dk/foredrag'"             => "href='/foredrag'",
        "href='/dk/konsulent'"            => "href='/konsulent'",
        "href='/dk/ai-konsulent'"         => "href='/ai-konsulent'",
        "href='/dk/eboeger'"              => "href='/eboger'",
        "href='/dk/hjemmeside'"           => "href='/hjemmeside'",
        "href='/dk/claude-cowork-kursus'" => "href='/claude-cowork-kursus'",
        "href='/dk/lp-klar-til-ai'"       => "href='/lp-klar-til-ai'",
        "href='/dk/privacy-policy'"       => "href='/privacy'",
        "href='/dk/terms-of-service'"     => "href='/terms'",
        "href='/dk/'"                     => "href='/'",
        "href='/dk'"                      => "href='/'",

        // Full URL variants
        'href="https://aibootcamphq.com/dk/gratis"'               => 'href="/gratis"',
        'href="https://aibootcamphq.com/dk/foredrag"'             => 'href="/foredrag"',
        'href="https://aibootcamphq.com/dk/konsulent"'            => 'href="/konsulent"',
        'href="https://aibootcamphq.com/dk/ai-konsulent"'         => 'href="/ai-konsulent"',
        'href="https://aibootcamphq.com/dk/eboeger"'              => 'href="/eboger"',
        'href="https://aibootcamphq.com/dk/hjemmeside"'           => 'href="/hjemmeside"',
        'href="https://aibootcamphq.com/dk/claude-cowork-kursus"' => 'href="/claude-cowork-kursus"',
        'href="https://aibootcamphq.com/dk/lp-klar-til-ai"'       => 'href="/lp-klar-til-ai"',
        'href="https://aibootcamphq.com/dk/privacy-policy"'       => 'href="/privacy"',
        'href="https://aibootcamphq.com/dk/terms-of-service"'     => 'href="/terms"',
        'href="https://aibootcamphq.com/dk/"'                     => 'href="/"',
        'href="https://aibootcamphq.com/dk"'                      => 'href="/"',
        'href="https://aibootcamphq.com/"'                        => 'href="/"',
        'href="https://aibootcamphq.com"'                         => 'href="/"',
    ];

    foreach ($linkReplacements as $old => $new) {
        $html = str_replace($old, $new, $html);
    }

    // Catch any remaining /dk/ relative links via regex
    // href="/dk/something" → href="/something"
    $html = preg_replace('/href="\/dk\/([^"]*)"/', 'href="/$1"', $html);
    $html = preg_replace("/href='\/dk\/([^']*)'/", "href='/$1'", $html);
    // href="https://aibootcamphq.com/dk/something" → href="/something"
    $html = preg_replace('/href="https?:\/\/aibootcamphq\.com\/dk\/([^"]*)"/', 'href="/$1"', $html);
    $html = preg_replace('/href="https?:\/\/aibootcamphq\.com\/dk"/', 'href="/"', $html);
    $html = preg_replace('/href="https?:\/\/aibootcamphq\.com\/"/', 'href="/"', $html);
    $html = preg_replace('/href="https?:\/\/aibootcamphq\.com"/', 'href="/"', $html);

    // Fix non-/dk/ prefixed links that got through (rendered without /dk/ in the HTML)
    $html = str_replace('href="/eboeger"', 'href="/eboger"', $html);
    $html = str_replace("href='/eboeger'", "href='/eboger'", $html);
    $html = str_replace('href="/privacy-policy"', 'href="/privacy"', $html);
    $html = str_replace("href='/privacy-policy'", "href='/privacy'", $html);
    $html = str_replace('href="/terms-of-service"', 'href="/terms"', $html);
    $html = str_replace("href='/terms-of-service'", "href='/terms'", $html);
    // /faq redirects to login on live site, point to contact instead
    $html = str_replace('href="/faq"', 'href="/contact"', $html);
    $html = str_replace("href='/faq'", "href='/contact'", $html);

    // 5. Rewire newsletter signup forms
    // Replace form action for newsletter popup: /nyhedsbrev/tilmeld → /api/newsletter
    $html = str_replace('action="/nyhedsbrev/tilmeld"', 'action="/api/newsletter"', $html);
    $html = str_replace("action='/nyhedsbrev/tilmeld'", "action='/api/newsletter'", $html);
    $html = str_replace('"/nyhedsbrev/tilmeld"', '"/api/newsletter"', $html);
    $html = str_replace("'/nyhedsbrev/tilmeld'", "'/api/newsletter'", $html);

    // Replace AJAX calls for newsletter
    $html = preg_replace(
        "/fetch\s*\(\s*['\"]\/nyhedsbrev\/tilmeld['\"]/",
        "fetch('/api/newsletter'",
        $html
    );
    $html = preg_replace(
        "/fetch\s*\(\s*['\"]https?:\/\/aibootcamphq\.com\/nyhedsbrev\/tilmeld['\"]/",
        "fetch('/api/newsletter'",
        $html
    );
    // Also catch /dk/nyhedsbrev/tilmeld
    $html = preg_replace(
        "/fetch\s*\(\s*['\"]\/dk\/nyhedsbrev\/tilmeld['\"]/",
        "fetch('/api/newsletter'",
        $html
    );

    // 6. Rewire contact form
    // Replace contact form AJAX: /api/contact-submit.php → /api/contact
    $html = str_replace('/api/contact-submit.php', '/api/contact', $html);
    $html = str_replace('"/api/contact-submit"', '"/api/contact"', $html);
    $html = str_replace("'/api/contact-submit'", "'/api/contact'", $html);

    // 7. Rewire course notification signups
    // Replace course-notify-signup: /api/course-notify-signup.php → /api/newsletter
    $html = str_replace('/api/course-notify-signup.php', '/api/newsletter', $html);
    $html = str_replace('/api/course-notify-signup', '/api/newsletter', $html);

    // 8. Fix any remaining absolute URLs to aibootcamphq.com in src/href
    $html = preg_replace('/src="https?:\/\/aibootcamphq\.com\/img\//', 'src="/uploads/3/img/', $html);

    // 9. Update canonical URLs and og:url meta tags to point to kompaza
    $html = preg_replace(
        '/content="https?:\/\/aibootcamphq\.com\/dk([^"]*)"/',
        'content="https://aibootcamphq.kompaza.com$1"',
        $html
    );
    $html = preg_replace(
        '/href="https?:\/\/aibootcamphq\.com\/dk([^"]*)"(\s*rel="canonical")/',
        'href="https://aibootcamphq.kompaza.com$1"$2',
        $html
    );

    return $html;
}

// =============================
// PROCESS EACH PAGE
// =============================

echo "\n=== Fetching and importing pages ===\n\n";

$results = [];

foreach ($pages as $page) {
    echo "--- {$page['slug']} ---\n";
    echo "  Fetching: https://aibootcamphq.com{$page['url']}\n";

    $html = fetchPage($page['url']);
    if ($html === false) {
        echo "  FAILED to fetch page. Skipping.\n\n";
        $results[] = ['slug' => $page['slug'], 'status' => 'FAILED', 'reason' => 'fetch error'];
        continue;
    }

    $originalSize = strlen($html);
    echo "  Fetched: " . number_format($originalSize) . " bytes\n";

    // Transform
    $html = transformHtml($html);
    $transformedSize = strlen($html);
    echo "  After transform: " . number_format($transformedSize) . " bytes\n";

    if ($transformedSize < 500) {
        echo "  WARNING: Content too small after transform, likely an error. Skipping.\n\n";
        $results[] = ['slug' => $page['slug'], 'status' => 'SKIPPED', 'reason' => 'too small'];
        continue;
    }

    // Extract meta description from HTML if available
    $metaDesc = '';
    if (preg_match('/<meta\s+name="description"\s+content="([^"]+)"/i', $html, $m)) {
        $metaDesc = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    }

    if ($dryRun) {
        echo "  [DRY RUN] Would upsert page '{$page['slug']}' ({$page['title']})\n";
        echo "  Meta: " . substr($metaDesc, 0, 80) . "...\n\n";
        $results[] = ['slug' => $page['slug'], 'status' => 'DRY RUN', 'size' => $transformedSize];
        continue;
    }

    // Upsert into custom_pages
    $stmt = $kompazaDb->prepare("SELECT id FROM custom_pages WHERE tenant_id = ? AND slug = ?");
    $stmt->execute([$tenantId, $page['slug']]);
    $existing = $stmt->fetch();

    if ($existing) {
        echo "  Updating existing page (id={$existing['id']})...\n";
        $stmt = $kompazaDb->prepare(
            "UPDATE custom_pages SET title = ?, content = ?, layout = 'full', meta_description = ?, status = 'published', is_homepage = ?, sort_order = ?, updated_at = NOW() WHERE id = ?"
        );
        $stmt->execute([
            $page['title'],
            $html,
            $metaDesc ?: null,
            $page['is_homepage'] ? 1 : 0,
            $page['sort_order'],
            $existing['id'],
        ]);
        echo "  Updated.\n";
        $results[] = ['slug' => $page['slug'], 'status' => 'UPDATED', 'id' => $existing['id'], 'size' => $transformedSize];
    } else {
        // If this is homepage, clear any existing homepage flag first
        if ($page['is_homepage']) {
            $stmt = $kompazaDb->prepare("UPDATE custom_pages SET is_homepage = 0 WHERE tenant_id = ? AND is_homepage = 1");
            $stmt->execute([$tenantId]);
            echo "  Cleared existing homepage flag.\n";
        }

        $stmt = $kompazaDb->prepare(
            "INSERT INTO custom_pages (tenant_id, slug, title, content, layout, meta_description, status, is_homepage, sort_order) VALUES (?, ?, ?, ?, 'full', ?, 'published', ?, ?)"
        );
        $stmt->execute([
            $tenantId,
            $page['slug'],
            $page['title'],
            $html,
            $metaDesc ?: null,
            $page['is_homepage'] ? 1 : 0,
            $page['sort_order'],
        ]);
        $newId = $kompazaDb->lastInsertId();
        echo "  Inserted (id=$newId).\n";
        $results[] = ['slug' => $page['slug'], 'status' => 'INSERTED', 'id' => $newId, 'size' => $transformedSize];
    }
    echo "\n";
}

// =============================
// Delete old pages that no longer match any slug
// =============================
$importedSlugs = array_column($pages, 'slug');
$placeholders = implode(',', array_fill(0, count($importedSlugs), '?'));
$stmt = $kompazaDb->prepare(
    "SELECT id, slug FROM custom_pages WHERE tenant_id = ? AND slug NOT IN ($placeholders)"
);
$stmt->execute(array_merge([$tenantId], $importedSlugs));
$orphanPages = $stmt->fetchAll();

if ($orphanPages) {
    echo "=== Orphan pages (not in import list) ===\n";
    foreach ($orphanPages as $orphan) {
        echo "  id={$orphan['id']} slug='{$orphan['slug']}' - keeping (may be manually created)\n";
    }
    echo "\n";
}

// =============================
// SUMMARY
// =============================
echo "=== IMPORT SUMMARY ===\n\n";
foreach ($results as $r) {
    $size = isset($r['size']) ? ' (' . number_format($r['size']) . ' bytes)' : '';
    $id = isset($r['id']) ? " id={$r['id']}" : '';
    echo "  {$r['slug']}: {$r['status']}{$id}{$size}\n";
}

echo "\n=== Done! ===\n";
echo "Pages accessible at:\n";
echo "  https://aibootcamphq.kompaza.com/ (homepage)\n";
foreach ($pages as $page) {
    if (!$page['is_homepage']) {
        echo "  https://aibootcamphq.kompaza.com/{$page['slug']}\n";
    }
}
echo "\n";
