#!/usr/bin/env php
<?php
/**
 * AI BootCamp HQ storefront refresh for Kompaza tenant `aibootcamphq`.
 *
 * Storefront-first: branding, catalog (ebooks + courses listings), freebies,
 * live marketing pages. Does NOT re-platform LMS videos or email campaigns.
 * Does NOT cut over aibootcamphq.com nginx.
 *
 * Usage (on app2):
 *   php scripts/import-aibootcamp-storefront.php --dry-run
 *   php scripts/import-aibootcamp-storefront.php
 *   php scripts/import-aibootcamp-storefront.php --pages=core
 *   php scripts/import-aibootcamp-storefront.php --skip-pages
 *   php scripts/import-aibootcamp-storefront.php --skip-assets
 *
 * Prerequisites:
 *   - catalog.yaml available (see CATALOG_CANDIDATES)
 *   - live site aibootcamphq.com reachable for page scrape
 */

declare(strict_types=1);

$dryRun = in_array('--dry-run', $argv, true);
$skipPages = in_array('--skip-pages', $argv, true);
$skipAssets = in_array('--skip-assets', $argv, true);
$pagesMode = 'all'; // core|all
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--pages=')) {
        $pagesMode = substr($arg, 8);
    }
}

$root = dirname(__DIR__);
$envFile = $root . '/.env';
if (!is_file($envFile)) {
    die("ERROR: .env not found at $envFile\n");
}
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
        continue;
    }
    [$k, $v] = explode('=', $line, 2);
    $_ENV[trim($k)] = trim($v, " \t\"'");
}

$pdo = new PDO(
    sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $_ENV['DB_HOST'] ?? '127.0.0.1',
        $_ENV['DB_PORT'] ?? '3306',
        $_ENV['DB_DATABASE'] ?? 'kompaza'
    ),
    $_ENV['DB_USERNAME'] ?? 'root',
    $_ENV['DB_PASSWORD'] ?? '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

$tenant = $pdo->query("SELECT * FROM tenants WHERE slug = 'aibootcamphq' LIMIT 1")->fetch();
if (!$tenant) {
    die("ERROR: tenant aibootcamphq not found\n");
}
$tid = (int) $tenant['id'];
if ($tid !== 3) {
    echo "NOTE: tenant id is $tid (expected 3 historically) — continuing with id=$tid\n";
}

echo "=== AI BootCamp storefront import ===\n";
echo "Tenant: {$tenant['name']} (id=$tid, slug={$tenant['slug']})\n";
echo "Mode: " . ($dryRun ? 'DRY RUN' : 'LIVE') . " pages=$pagesMode\n\n";

// ---------------------------------------------------------------------------
// Paths
// ---------------------------------------------------------------------------
$catalogCandidates = [
    $root . '/storage/imports/aibootcamp/catalog.yaml',
    '/Users/jh/GitHub/aibootcamp_kursus/data/catalog.yaml',
    '/var/www/html/aibootcamphq.com/data/catalog.yaml',
    '/var/www/html/kursus.aibootcamp.dk/data/catalog.yaml',
];
$catalogPath = null;
foreach ($catalogCandidates as $c) {
    if (is_file($c)) {
        $catalogPath = $c;
        break;
    }
}
if (!$catalogPath) {
    die("ERROR: catalog.yaml not found. Copy to storage/imports/aibootcamp/catalog.yaml\n");
}
echo "Catalog: $catalogPath\n";

$imgSources = [
    '/var/www/html/aibootcamphq.com/public/img',
    '/var/www/html/kursus.aibootcamp.dk/public/img',
];
$bookSources = [
    '/var/www/html/aibootcamphq.com/storage/books',
    '/var/www/html/kursus.aibootcamp.dk/storage/books',
    '/Users/jh/GitHub/aibootcamp_kursus/storage/books',
];
$uploadImg = $root . "/public/uploads/{$tid}/img";
$pdfDir = $root . "/storage/pdfs/{$tid}";
$htmlCache = $root . "/storage/imports/aibootcamp/html";

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------
function info(string $msg): void
{
    echo "  → $msg\n";
}

function parseYamlScalar(string $raw)
{
    $raw = trim($raw);
    if ($raw === '' || $raw === 'null' || $raw === '~') {
        return null;
    }
    if ($raw === 'true') {
        return true;
    }
    if ($raw === 'false') {
        return false;
    }
    if (preg_match('/^-?\d+(\.\d+)?$/', $raw)) {
        return str_contains($raw, '.') ? (float) $raw : (int) $raw;
    }
    if (
        (str_starts_with($raw, "'") && str_ends_with($raw, "'")) ||
        (str_starts_with($raw, '"') && str_ends_with($raw, '"'))
    ) {
        $inner = substr($raw, 1, -1);
        return str_replace(["\\'", '\\"'], ["'", '"'], $inner);
    }
    return $raw;
}

/**
 * Minimal YAML parser for catalog.yaml shape (sections of list maps).
 * @return array<string, list<array<string,mixed>>>
 */
function parseCatalogYaml(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    $sections = [];
    $section = null;
    $current = null;

    $flush = static function () use (&$sections, &$section, &$current): void {
        if ($section !== null && $current !== null && $current !== []) {
            $sections[$section][] = $current;
        }
        $current = null;
    };

    foreach ($lines as $line) {
        if ($line === '' || preg_match('/^\s*#/', $line)) {
            continue;
        }
        // Top-level key
        if (preg_match('/^([a-z_]+):\s*$/', $line, $m)) {
            $flush();
            $section = $m[1];
            if (!isset($sections[$section])) {
                $sections[$section] = [];
            }
            continue;
        }
        if ($section === null) {
            continue;
        }
        // List item start: "  - slug: x" or "  -"
        if (preg_match('/^  -\s*$/', $line)) {
            $flush();
            $current = [];
            continue;
        }
        if (preg_match('/^  - (\w+):\s*(.*)$/', $line, $m)) {
            $flush();
            $current = [$m[1] => parseYamlScalar($m[2])];
            continue;
        }
        // Nested key under item
        if ($current !== null && preg_match('/^    (\w+):\s*(.*)$/', $line, $m)) {
            $current[$m[1]] = parseYamlScalar($m[2]);
            continue;
        }
        // Nested list under item (topics: - x) — skip body, keep key if first line
        if ($current !== null && preg_match('/^    - /', $line)) {
            continue;
        }
    }
    $flush();
    return $sections;
}

function slugify(string $s): string
{
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? $s;
    return trim($s, '-') ?: 'item';
}

function urlToSlug(?string $url): ?string
{
    if ($url === null || $url === '') {
        return null;
    }
    $path = parse_url($url, PHP_URL_PATH) ?: $url;
    $path = preg_replace('#^/dk/?#', '', $path) ?? $path;
    $path = trim($path, '/');
    if ($path === '') {
        return 'homepage';
    }
    return $path;
}

function findPdf(array $bookDirs, string $slug, ?string $pdfFile, ?string $cover): ?array
{
    $candidates = [];
    if ($pdfFile) {
        $candidates[] = basename($pdfFile);
    }
    $candidates[] = $slug . '.pdf';
    $candidates[] = str_replace('-', '_', $slug) . '.pdf';
    if ($cover) {
        $base = pathinfo(basename($cover), PATHINFO_FILENAME);
        $candidates[] = $base . '.pdf';
        $candidates[] = preg_replace('/-cover(-\d+)?$/', '', $base) . '.pdf';
    }
    // Known freebie renames
    $aliases = [
        'chatgpt-atlas-guide' => 'atlas-final.pdf',
        'atlas' => 'atlas-final.pdf',
        'gdpr' => 'ai-gdpr-arbejdspladsen.pdf',
        'claude-cowork' => 'claude-cowork.pdf',
        'linkedin' => 'linkedin-leadmaskine.pdf',
    ];
    if (isset($aliases[$slug])) {
        array_unshift($candidates, $aliases[$slug]);
    }

    foreach ($bookDirs as $dir) {
        if (!is_dir($dir)) {
            continue;
        }
        foreach ($candidates as $name) {
            $full = $dir . '/' . $name;
            if (is_file($full)) {
                return ['path' => $full, 'name' => $name];
            }
        }
        // Fuzzy: file starts with slug
        foreach (scandir($dir) ?: [] as $f) {
            if (!str_ends_with(strtolower($f), '.pdf')) {
                continue;
            }
            if (str_starts_with(strtolower($f), strtolower($slug))) {
                return ['path' => $dir . '/' . $f, 'name' => $f];
            }
        }
    }
    return null;
}

function copyFileSafe(string $from, string $to, bool $dryRun): bool
{
    if ($dryRun) {
        return true;
    }
    $dir = dirname($to);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return copy($from, $to);
}

// ---------------------------------------------------------------------------
// A. Assets
// ---------------------------------------------------------------------------
echo "\nA. Assets\n";
if (!$skipAssets) {
    $imgSrc = null;
    foreach ($imgSources as $d) {
        if (is_dir($d)) {
            $imgSrc = $d;
            break;
        }
    }
    if ($imgSrc) {
        info("Images from $imgSrc → $uploadImg");
        if (!$dryRun) {
            if (!is_dir($uploadImg)) {
                mkdir($uploadImg, 0755, true);
            }
            // rsync-like copy
            $cmd = sprintf('cp -a %s/. %s/ 2>/dev/null', escapeshellarg($imgSrc), escapeshellarg($uploadImg));
            exec($cmd, $out, $code);
            // logo
            foreach (['logo.png', 'logo.svg', 'aibootcamp-logo.png'] as $logo) {
                if (is_file("$imgSrc/$logo")) {
                    copy("$imgSrc/$logo", $root . "/public/uploads/{$tid}/logo.png");
                    break;
                }
            }
        }
        $n = is_dir($uploadImg) ? count(glob($uploadImg . '/*') ?: []) : 0;
        info("Image files present: $n");
    } else {
        info('WARNING: no live img directory found');
    }

    if (!$dryRun && !is_dir($pdfDir)) {
        mkdir($pdfDir, 0755, true);
    }
    if (!$dryRun && !is_dir($htmlCache)) {
        mkdir($htmlCache, 0755, true);
    }
} else {
    info('Skipped (--skip-assets)');
}

$bookDirs = array_values(array_filter($bookSources, 'is_dir'));
info('Book dirs: ' . (empty($bookDirs) ? '(none)' : implode(', ', $bookDirs)));

// ---------------------------------------------------------------------------
// Parse catalog
// ---------------------------------------------------------------------------
echo "\nB. Parse catalog\n";
$catalog = parseCatalogYaml($catalogPath);
$coursesCat = $catalog['courses'] ?? [];
$booksCat = $catalog['books'] ?? [];
$freebiesCat = $catalog['freebies'] ?? [];
info(sprintf('courses=%d books=%d freebies=%d', count($coursesCat), count($booksCat), count($freebiesCat)));

// ---------------------------------------------------------------------------
// C. Wipe marketing content (safe)
// ---------------------------------------------------------------------------
echo "\nC. Wipe marketing content (preserve users/orders/enrollments)\n";

// Enrolled course ids must not be deleted
$enrolledCourseIds = $pdo->query("
    SELECT DISTINCT c.id, c.slug
    FROM courses c
    INNER JOIN course_enrollments ce ON ce.course_id = c.id
    WHERE c.tenant_id = {$tid}
")->fetchAll();
$enrolledIds = array_map(static fn($r) => (int) $r['id'], $enrolledCourseIds);
$enrolledSlugs = array_map(static fn($r) => $r['slug'], $enrolledCourseIds);
info('Enrolled courses preserved: ' . (empty($enrolledSlugs) ? '(none)' : implode(', ', $enrolledSlugs)));

if (!$dryRun) {
    $pdo->beginTransaction();
    try {
        $pdo->exec("DELETE FROM custom_pages WHERE tenant_id = {$tid}");
        info('Deleted custom_pages');

        $pdo->exec("DELETE FROM lead_magnets WHERE tenant_id = {$tid}");
        info('Deleted lead_magnets');

        // ebooks: no purchases for this tenant
        $pdo->exec("DELETE FROM ebooks WHERE tenant_id = {$tid}");
        info('Deleted ebooks');

        // products: 0 rows expected; FK sets order_items.product_id NULL
        $pdo->exec("DELETE FROM products WHERE tenant_id = {$tid}");
        info('Deleted products');

        // courses without enrollments
        if (empty($enrolledIds)) {
            $pdo->exec("DELETE FROM courses WHERE tenant_id = {$tid}");
            info('Deleted all courses (no enrollments)');
        } else {
            $in = implode(',', $enrolledIds);
            $pdo->exec("DELETE FROM courses WHERE tenant_id = {$tid} AND id NOT IN ($in)");
            info("Deleted non-enrolled courses; kept ids $in");
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        die('WIPE FAILED: ' . $e->getMessage() . "\n");
    }
} else {
    info('[dry-run] would wipe custom_pages, lead_magnets, ebooks, products, non-enrolled courses');
}

// ---------------------------------------------------------------------------
// D. Branding
// ---------------------------------------------------------------------------
echo "\nD. Branding\n";
$brand = [
    'company_name' => 'AI BootCamp HQ',
    'tagline' => 'Online AI-kurser, e-bøger & certificering for ledere og vidensarbejdere',
    'primary_color' => '#2563eb',
    'secondary_color' => '#9333ea',
    'email' => 'info@aibootcamp.dk',
    'currency' => 'DKK',
    'logo_url' => "/uploads/{$tid}/logo.png",
];
if (!$dryRun) {
    $stmt = $pdo->prepare("
        UPDATE tenants SET
            company_name = ?, tagline = ?, primary_color = ?, secondary_color = ?,
            email = ?, currency = ?, logo_url = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([
        $brand['company_name'],
        $brand['tagline'],
        $brand['primary_color'],
        $brand['secondary_color'],
        $brand['email'],
        $brand['currency'],
        $brand['logo_url'],
        $tid,
    ]);
}
info('Brand fields set (blue/purple gradient)');

// ---------------------------------------------------------------------------
// E. Courses from catalog
// ---------------------------------------------------------------------------
echo "\nE. Courses\n";
$courseStats = ['updated' => 0, 'inserted' => 0, 'skipped' => 0];

// Map catalog slugs → existing enrolled slugs when names match known pairs
$slugAliases = [
    'eu-ai-act-kursus' => 'eu-ai-act-compliance',
    'claude-cowork-kursus' => 'claude-cowork-masterclass',
    'claude-office-kursus' => 'claude-i-office',
];

foreach ($coursesCat as $c) {
    $slug = (string) ($c['slug'] ?? '');
    if ($slug === '') {
        $courseStats['skipped']++;
        continue;
    }
    $title = (string) ($c['name'] ?? $slug);
    $desc = (string) ($c['description'] ?? '');
    $statusRaw = (string) ($c['status'] ?? '');
    $isActive = stripos($statusRaw, 'Aktiv') !== false || strcasecmp($statusRaw, 'active') === 0;
    $status = $isActive ? 'published' : 'draft';
    $price = $c['price_dkk'] ?? null;
    if ($price === null || $price === '') {
        $price = null;
    } else {
        $price = (float) $price;
    }
    $pricingType = ($price !== null && $price > 0) ? 'one_time' : 'free';
    $subtitle = (string) ($c['tagline'] ?? $c['format'] ?? '');

    // Prefer alias for enrolled courses
    $dbSlug = $slugAliases[$slug] ?? $slug;

    if ($dryRun) {
        info("[dry-run] course $dbSlug ($status) price=" . ($price ?? 'null'));
        $courseStats['inserted']++;
        continue;
    }

    $stmt = $pdo->prepare('SELECT id FROM courses WHERE tenant_id = ? AND slug = ?');
    $stmt->execute([$tid, $dbSlug]);
    $row = $stmt->fetch();

    // Also try original catalog slug
    if (!$row && $dbSlug !== $slug) {
        $stmt->execute([$tid, $slug]);
        $row = $stmt->fetch();
        if ($row) {
            $dbSlug = $slug;
        }
    }

    if ($row) {
        $upd = $pdo->prepare("
            UPDATE courses SET title = ?, subtitle = ?, description = ?, short_description = ?,
                pricing_type = ?, price_dkk = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $upd->execute([
            $title,
            $subtitle ?: null,
            $desc ?: null,
            mb_substr($desc, 0, 500) ?: null,
            $pricingType,
            $price,
            $status,
            $row['id'],
        ]);
        $courseStats['updated']++;
        info("Updated course {$dbSlug} (#{$row['id']})");
    } else {
        $ins = $pdo->prepare("
            INSERT INTO courses (tenant_id, slug, title, subtitle, description, short_description,
                pricing_type, price_dkk, status, instructor_name, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Jannik Hansen', NOW(), NOW())
        ");
        $ins->execute([
            $tid,
            $dbSlug,
            $title,
            $subtitle ?: null,
            $desc ?: null,
            mb_substr($desc, 0, 500) ?: null,
            $pricingType,
            $price,
            $status,
        ]);
        $courseStats['inserted']++;
        info("Inserted course {$dbSlug}");
    }
}
info(json_encode($courseStats));

// ---------------------------------------------------------------------------
// F. Books → ebooks
// ---------------------------------------------------------------------------
echo "\nF. Books → ebooks\n";
$ebookStats = ['inserted' => 0, 'skipped' => 0, 'pdfs' => 0];

foreach ($booksCat as $b) {
    $slug = (string) ($b['slug'] ?? '');
    if ($slug === '') {
        $ebookStats['skipped']++;
        continue;
    }
    $title = (string) ($b['name'] ?? $slug);
    $desc = (string) ($b['description'] ?? '');
    $statusRaw = (string) ($b['status'] ?? 'Aktiv');
    $isActive = stripos($statusRaw, 'Aktiv') !== false || strcasecmp($statusRaw, 'active') === 0;
    // Include campaign/legacy active products; archive explicit udfaset if marked
    if (stripos($statusRaw, 'udfaset') !== false || stripos($statusRaw, 'arkiv') !== false) {
        $status = 'archived';
    } else {
        $status = $isActive ? 'published' : 'draft';
    }
    $price = isset($b['price_dkk']) && $b['price_dkk'] !== null && $b['price_dkk'] !== ''
        ? (float) $b['price_dkk']
        : 0.0;
    $cover = $b['cover'] ?? null;
    $coverPath = null;
    if (is_string($cover) && $cover !== '') {
        // /img/foo.png → /uploads/{tid}/img/foo.png
        $coverPath = preg_replace('#^/img/#', "/uploads/{$tid}/img/", $cover);
    }

    $pdf = findPdf($bookDirs, $slug, isset($b['pdf_file']) ? (string) $b['pdf_file'] : null, is_string($cover) ? $cover : null);
    $pdfFilename = null;
    $pdfOriginal = null;
    if ($pdf) {
        $pdfFilename = $pdf['name'];
        $pdfOriginal = $pdf['name'];
        $dest = $pdfDir . '/' . $pdf['name'];
        if (!$dryRun) {
            copyFileSafe($pdf['path'], $dest, false);
        }
        $ebookStats['pdfs']++;
    }

    $audience = $b['audience'] ?? null;
    // target_audience column requires valid JSON (CHECK json_valid)
    $audienceJson = null;
    if (is_string($audience) && $audience !== '') {
        $audienceJson = json_encode([$audience], JSON_UNESCAPED_UNICODE);
    }
    $meta = mb_substr($desc, 0, 300);

    if ($dryRun) {
        info("[dry-run] ebook $slug price=$price pdf=" . ($pdfFilename ?? '-'));
        $ebookStats['inserted']++;
        continue;
    }

    $subtitleRaw = isset($b['tagline']) ? (string) $b['tagline'] : (isset($b['format']) ? (string) $b['format'] : '');
    $subtitle = $subtitleRaw !== '' ? mb_substr($subtitleRaw, 0, 500) : null;
    $heroHeadline = mb_substr($title, 0, 500);
    $heroSub = isset($b['format']) ? mb_substr((string) $b['format'], 0, 1000) : null;
    $meta = $meta !== '' ? mb_substr($meta, 0, 500) : null;

    $ins = $pdo->prepare("
        INSERT INTO ebooks (
            tenant_id, slug, title, subtitle, description, hero_headline, hero_subheadline,
            cover_image_path, target_audience, pdf_filename, pdf_original_name,
            price_dkk, meta_description, status, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $ins->execute([
        $tid,
        $slug,
        mb_substr($title, 0, 255),
        $subtitle,
        $desc ?: null,
        $heroHeadline,
        $heroSub,
        $coverPath,
        $audienceJson,
        $pdfFilename,
        $pdfOriginal,
        $price,
        $meta,
        $status,
    ]);
    $ebookStats['inserted']++;
}
info(json_encode($ebookStats));

// ---------------------------------------------------------------------------
// G. Freebies → lead_magnets
// ---------------------------------------------------------------------------
echo "\nG. Freebies → lead_magnets\n";
$lmStats = ['inserted' => 0, 'skipped' => 0, 'pdfs' => 0];

$usedLmSlugs = [];
foreach ($freebiesCat as $f) {
    $slug = (string) ($f['slug'] ?? '');
    if ($slug === '') {
        $lmStats['skipped']++;
        continue;
    }
    // Prefer stable catalog slug (url-derived slugs often collide e.g. multiple → "gratis")
    $urlSlug = urlToSlug(isset($f['url']) ? (string) $f['url'] : null);
    $lmSlug = $slug;
    if ($urlSlug && !isset($usedLmSlugs[$urlSlug]) && !isset($usedLmSlugs[$slug])) {
        // only use url slug when unique and not already used
        // keep catalog slug for uniqueness
    }
    if (isset($usedLmSlugs[$lmSlug])) {
        $lmSlug = $slug . '-' . substr(md5($title = (string) ($f['name'] ?? $slug)), 0, 6);
    }
    $usedLmSlugs[$lmSlug] = true;
    $title = (string) ($f['name'] ?? $slug);
    $desc = (string) ($f['description'] ?? '');
    $statusRaw = (string) ($f['status'] ?? 'Aktiv');
    $status = (stripos($statusRaw, 'Aktiv') !== false) ? 'published' : 'draft';

    $pdf = findPdf(
        $bookDirs,
        $slug,
        isset($f['pdf_file']) ? (string) $f['pdf_file'] : null,
        isset($f['cover']) && is_string($f['cover']) ? $f['cover'] : null
    );
    $pdfFilename = null;
    $pdfOriginal = null;
    if ($pdf) {
        $pdfFilename = $pdf['name'];
        $pdfOriginal = $pdf['name'];
        if (!$dryRun) {
            copyFileSafe($pdf['path'], $pdfDir . '/' . $pdf['name'], false);
            // Also store under lead-magnet expected path if different
            // Kompaza typically uses storage/pdfs/{tenant}/ for ebooks; lead magnets use pdf_filename in storage
        }
        $lmStats['pdfs']++;
    }

    $coverPath = null;
    if (isset($f['cover']) && is_string($f['cover']) && $f['cover'] !== '') {
        $coverPath = preg_replace('#^/img/#', "/uploads/{$tid}/img/", $f['cover']);
    }

    if ($dryRun) {
        info("[dry-run] freebie $lmSlug pdf=" . ($pdfFilename ?? '-'));
        $lmStats['inserted']++;
        continue;
    }

    $ins = $pdo->prepare("
        INSERT INTO lead_magnets (
            tenant_id, slug, title, subtitle, meta_description, hero_headline, hero_subheadline,
            hero_cta_text, cover_image_path, pdf_filename, pdf_original_name,
            email_subject, email_body_html, status, template, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Download gratis', ?, ?, ?, ?, ?, ?, 'bold', NOW(), NOW())
    ");
    $emailSubject = 'Din gratis download: ' . $title;
    $emailBody = '<p>Hej {{first_name}},</p><p>Tak for din tilmelding. Her er dit download-link til <strong>'
        . htmlspecialchars($title) . '</strong>:</p><p><a href="{{download_url}}">Download nu</a></p>';
    $ins->execute([
        $tid,
        $lmSlug,
        $title,
        isset($f['format']) ? (string) $f['format'] : null,
        mb_substr($desc, 0, 300) ?: null,
        $title,
        $desc ?: null,
        $coverPath,
        $pdfFilename,
        $pdfOriginal,
        $emailSubject,
        $emailBody,
        $status,
    ]);
    $lmStats['inserted']++;
}
info(json_encode($lmStats));

// ---------------------------------------------------------------------------
// H. Live pages scrape
// ---------------------------------------------------------------------------
echo "\nH. Live marketing pages\n";

if ($skipPages) {
    info('Skipped (--skip-pages)');
} else {
    $corePages = [
        ['url' => '/dk', 'slug' => 'homepage', 'title' => 'AI BootCamp HQ', 'is_homepage' => true, 'sort_order' => 0],
        ['url' => '/dk/gratis', 'slug' => 'gratis', 'title' => "Gratis PDF'er & AI Guides", 'is_homepage' => false, 'sort_order' => 1],
        ['url' => '/dk/foredrag', 'slug' => 'foredrag', 'title' => 'Foredrag om AI', 'is_homepage' => false, 'sort_order' => 2],
        ['url' => '/dk/konsulent', 'slug' => 'konsulent', 'title' => 'AI Konsulent', 'is_homepage' => false, 'sort_order' => 3],
        ['url' => '/dk/ai-konsulent', 'slug' => 'ai-konsulent', 'title' => 'AI Konsulenterne', 'is_homepage' => false, 'sort_order' => 4],
        ['url' => '/dk/mba', 'slug' => 'mba', 'title' => 'MBA i AI', 'is_homepage' => false, 'sort_order' => 5],
        ['url' => '/dk/hjemmeside', 'slug' => 'hjemmeside', 'title' => 'Byg hjemmeside med AI', 'is_homepage' => false, 'sort_order' => 6],
        ['url' => '/dk/lp-klar-til-ai', 'slug' => 'lp-klar-til-ai', 'title' => 'AI Klar Til Kamp', 'is_homepage' => false, 'sort_order' => 7],
        ['url' => '/dk/eboeger', 'slug' => 'eboeger-hub', 'title' => 'E-bøger', 'is_homepage' => false, 'sort_order' => 8],
        ['url' => '/dk/privacy-policy', 'slug' => 'privacy', 'title' => 'Privatlivspolitik', 'is_homepage' => false, 'sort_order' => 90],
        ['url' => '/dk/terms-of-service', 'slug' => 'terms', 'title' => 'Handelsbetingelser', 'is_homepage' => false, 'sort_order' => 91],
    ];

    $pages = $corePages;
    $seen = [];
    foreach ($corePages as $p) {
        $seen[$p['slug']] = true;
    }

    if ($pagesMode === 'all') {
        $sort = 20;
        foreach (array_merge($coursesCat, $booksCat, $freebiesCat) as $item) {
            $url = $item['url'] ?? null;
            if (!is_string($url) || $url === '' || $url === 'null') {
                continue;
            }
            // Skip purchase/success endpoints for storefront static import
            if (preg_match('#(-koeb|/success|/purchase|/tak)(/|$)#', $url)) {
                continue;
            }
            $slug = urlToSlug($url);
            if (!$slug || isset($seen[$slug])) {
                continue;
            }
            $seen[$slug] = true;
            $pages[] = [
                'url' => str_starts_with($url, '/') ? $url : '/' . $url,
                'slug' => $slug,
                'title' => (string) ($item['name'] ?? $slug),
                'is_homepage' => false,
                'sort_order' => $sort++,
            ];
        }
    }

    info('Pages to fetch: ' . count($pages));

    $pageStats = ['ok' => 0, 'fail' => 0, 'skip' => 0];

    $fetchPage = static function (string $url, string $cacheDir, bool $dryRun) use ($tid): string|false {
        $cacheKey = $cacheDir . '/' . md5($url) . '.html';
        if (is_file($cacheKey) && filesize($cacheKey) > 500) {
            return (string) file_get_contents($cacheKey);
        }
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://aibootcamphq.com' . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_USERAGENT => 'Kompaza-AIBC-Import/2.0',
            CURLOPT_HTTPHEADER => ['Accept-Language: da,en;q=0.8'],
        ]);
        $html = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($html === false || $code !== 200 || strlen((string) $html) < 500) {
            return false;
        }
        if (!$dryRun) {
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            file_put_contents($cacheKey, $html);
        }
        return (string) $html;
    };

    $transformHtml = static function (string $html) use ($tid): string {
        $upload = "/uploads/{$tid}/img/";
        $html = str_replace(
            ['src="/img/', "src='/img/", 'srcset="/img/', "srcset='/img/", 'url(/img/', 'url("/img/', "url('/img/", 'content="/img/', 'href="/img/'],
            ["src=\"{$upload}", "src='{$upload}", "srcset=\"{$upload}", "srcset='{$upload}", "url({$upload}", "url(\"{$upload}", "url('{$upload}", "content=\"{$upload}", "href=\"{$upload}"],
            $html
        );
        $html = preg_replace('#([,\s])/img/#', '$1' . $upload, $html) ?? $html;
        $html = preg_replace('#https?://(www\.)?aibootcamphq\.com/img/#i', $upload, $html) ?? $html;

        // Analytics / chat
        $html = preg_replace('/<!-- Matomo -->.*?<!-- End Matomo Code -->/s', '', $html) ?? $html;
        $html = preg_replace('/<!-- Matomo Tag Manager -->.*?<!-- End Matomo Tag Manager -->/s', '', $html) ?? $html;
        $html = preg_replace('/<!-- Brevo Conversations.*?<\/script>/s', '', $html) ?? $html;

        // /dk paths → relative without prefix
        $html = preg_replace('#href="https?://(www\.)?aibootcamphq\.com/dk/([^"]*)"#i', 'href="/$2"', $html) ?? $html;
        $html = preg_replace('#href="https?://(www\.)?aibootcamphq\.com/dk"#i', 'href="/"', $html) ?? $html;
        $html = preg_replace('#href="https?://(www\.)?aibootcamphq\.com/"#i', 'href="/"', $html) ?? $html;
        $html = preg_replace('#href="/dk/([^"]*)"#', 'href="/$1"', $html) ?? $html;
        $html = preg_replace('#href="/dk"#', 'href="/"', $html) ?? $html;
        $html = str_replace(['href="/eboeger"', "href='/eboeger'"], ['href="/eboger"', "href='/eboger'"], $html);
        // Use ~ delimiter so # anchors in the pattern are not special
        $html = preg_replace('~href="/privacy-policy(#[^"]*)?"~', 'href="/privacy$1"', $html) ?? $html;
        $html = str_replace('href="/terms-of-service"', 'href="/terms"', $html);

        // Forms → kompaza APIs where known
        $html = str_replace(
            ['/nyhedsbrev/tilmeld', '/api/contact-submit.php', '/api/course-notify-signup.php'],
            ['/api/newsletter', '/api/contact', '/api/newsletter'],
            $html
        );

        // Canonical / og:url
        $html = preg_replace(
            '#content="https?://(www\.)?aibootcamphq\.com/dk([^"]*)"#i',
            'content="https://aibootcamphq.kompaza.com$2"',
            $html
        ) ?? $html;

        return $html;
    };

    foreach ($pages as $page) {
        echo "  · {$page['slug']} ({$page['url']}) ... ";
        $html = $fetchPage($page['url'], $htmlCache, $dryRun);
        if ($html === false) {
            echo "FAIL fetch\n";
            $pageStats['fail']++;
            usleep(150000);
            continue;
        }
        $html = $transformHtml($html);
        if (strlen($html) < 500) {
            echo "SKIP small\n";
            $pageStats['skip']++;
            continue;
        }
        $metaDesc = null;
        if (preg_match('/<meta\s+name=["\']description["\']\s+content=["\']([^"\']+)["\']/i', $html, $m)) {
            $metaDesc = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
        }

        if ($dryRun) {
            echo 'OK dry-run ' . number_format(strlen($html)) . " bytes\n";
            $pageStats['ok']++;
            continue;
        }

        if (!empty($page['is_homepage'])) {
            $pdo->prepare("UPDATE custom_pages SET is_homepage = 0 WHERE tenant_id = ?")->execute([$tid]);
        }

        $ins = $pdo->prepare("
            INSERT INTO custom_pages (tenant_id, slug, title, content, layout, meta_description, status, is_homepage, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, 'full', ?, 'published', ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content), layout = 'full',
                meta_description = VALUES(meta_description), status = 'published',
                is_homepage = VALUES(is_homepage), sort_order = VALUES(sort_order), updated_at = NOW()
        ");
        // custom_pages unique is (tenant_id, slug) — ON DUPLICATE needs unique key
        try {
            $ins->execute([
                $tid,
                $page['slug'],
                $page['title'],
                $html,
                $metaDesc,
                !empty($page['is_homepage']) ? 1 : 0,
                $page['sort_order'],
            ]);
        } catch (PDOException $e) {
            // Fallback if no unique key on (tenant_id,slug)
            $ex = $pdo->prepare('SELECT id FROM custom_pages WHERE tenant_id = ? AND slug = ?');
            $ex->execute([$tid, $page['slug']]);
            $existing = $ex->fetch();
            if ($existing) {
                $pdo->prepare("
                    UPDATE custom_pages SET title=?, content=?, layout='full', meta_description=?, status='published',
                    is_homepage=?, sort_order=?, updated_at=NOW() WHERE id=?
                ")->execute([
                    $page['title'], $html, $metaDesc,
                    !empty($page['is_homepage']) ? 1 : 0, $page['sort_order'], $existing['id'],
                ]);
            } else {
                $pdo->prepare("
                    INSERT INTO custom_pages (tenant_id, slug, title, content, layout, meta_description, status, is_homepage, sort_order)
                    VALUES (?, ?, ?, ?, 'full', ?, 'published', ?, ?)
                ")->execute([
                    $tid, $page['slug'], $page['title'], $html, $metaDesc,
                    !empty($page['is_homepage']) ? 1 : 0, $page['sort_order'],
                ]);
            }
        }
        echo 'OK ' . number_format(strlen($html)) . " bytes\n";
        $pageStats['ok']++;
        usleep(120000);
    }
    info(json_encode($pageStats));
}

// ---------------------------------------------------------------------------
// I. Permissions + summary
// ---------------------------------------------------------------------------
echo "\nI. Finish\n";
if (!$dryRun) {
    @exec('chown -R www-data:www-data ' . escapeshellarg($root . "/public/uploads/{$tid}") . ' ' . escapeshellarg($pdfDir) . ' 2>/dev/null');
}

$counts = [
    'custom_pages' => (int) $pdo->query("SELECT COUNT(*) FROM custom_pages WHERE tenant_id={$tid}")->fetchColumn(),
    'ebooks' => (int) $pdo->query("SELECT COUNT(*) FROM ebooks WHERE tenant_id={$tid}")->fetchColumn(),
    'courses' => (int) $pdo->query("SELECT COUNT(*) FROM courses WHERE tenant_id={$tid}")->fetchColumn(),
    'lead_magnets' => (int) $pdo->query("SELECT COUNT(*) FROM lead_magnets WHERE tenant_id={$tid}")->fetchColumn(),
    'users' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE tenant_id={$tid}")->fetchColumn(),
    'orders' => (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE tenant_id={$tid}")->fetchColumn(),
];

echo "\n=== DONE ===\n";
foreach ($counts as $k => $v) {
    echo "  $k: $v\n";
}
echo "\nCheck:\n";
echo "  https://aibootcamphq.kompaza.com/\n";
echo "  https://aibootcamphq.kompaza.com/eboger\n";
echo "  https://aibootcamphq.kompaza.com/kurser\n";
echo "  https://aibootcamphq.kompaza.com/gratis\n";
echo ($dryRun ? "\n(This was a dry run — re-run without --dry-run)\n" : "\n");
