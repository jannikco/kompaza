<?php
/**
 * Phase 1 full import for jannikhansen tenant:
 * - All library ebooks from library.yaml + PDFs/covers
 * - Office / Creator / Founder courses from JH DB modules+lessons
 * - Free-book lead magnets
 * - Workshop email sequence (placeholder steps)
 * - Extra redirects
 *
 * Run on app2:
 *   php scripts/import-jannikhansen-phase1.php
 */

$dryRun = in_array('--dry-run', $argv, true);

$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) die("ERROR: .env missing\n");
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
    [$k, $v] = explode('=', $line, 2);
    $_ENV[trim($k)] = trim($v, " \t\"'");
}

$pdo = new PDO(
    sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $_ENV['DB_HOST'] ?? 'localhost',
        $_ENV['DB_PORT'] ?? '3306',
        $_ENV['DB_DATABASE'] ?? 'kompaza'
    ),
    $_ENV['DB_USERNAME'] ?? 'root',
    $_ENV['DB_PASSWORD'] ?? '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

// JH DB — try socket root first (server ops), then env credentials, then fail soft
$jhPdo = null;
$jhTries = [
    ['mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=jannikhansen;charset=utf8mb4', 'root', ''],
    ['mysql:host=127.0.0.1;dbname=jannikhansen;charset=utf8mb4', 'root', ''],
    [
        sprintf('mysql:host=%s;port=%s;dbname=jannikhansen;charset=utf8mb4',
            $_ENV['DB_HOST'] ?? 'localhost',
            $_ENV['DB_PORT'] ?? '3306'
        ),
        $_ENV['DB_USERNAME'] ?? 'root',
        $_ENV['DB_PASSWORD'] ?? '',
    ],
];
foreach ($jhTries as [$dsn, $user, $pass]) {
    try {
        $jhPdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        echo "JH DB connected via $user\n";
        break;
    } catch (Exception $e) {
        // try next
    }
}
if (!$jhPdo) {
    die("ERROR: cannot connect to jannikhansen database (need root socket or grants)\n");
}

$tenant = $pdo->query("SELECT * FROM tenants WHERE slug='jannikhansen' LIMIT 1")->fetch();
if (!$tenant) die("ERROR: run 032 first\n");
$tid = (int)$tenant['id'];
echo "=== Phase 1 import tenant=$tid ===\n";

$jhRoot = '/var/www/html/jannikhansen.com';
if (!is_dir($jhRoot)) {
    $jhRoot = '/Users/jh/GitHub/jannikhansen';
}
$pdfDir = __DIR__ . '/../storage/pdfs/' . $tid;
$uploadDir = __DIR__ . '/../public/uploads/' . $tid;
@mkdir($pdfDir, 0755, true);

// Ensure ebook_purchases exists
try {
    $pdo->exec(file_get_contents(__DIR__ . '/../database/migrations/033_ebook_purchases.sql'));
    echo "ebook_purchases OK\n";
} catch (Exception $e) {
    echo "ebook_purchases note: " . $e->getMessage() . "\n";
}

// ---------- LIBRARY ----------
echo "\n--- Library ebooks ---\n";
require_once $jhRoot . '/vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

$lib = Yaml::parseFile($jhRoot . '/data/library.yaml');
$tiers = $lib['tiers'] ?? [];
$books = $lib['books'] ?? [];
$ebookCount = 0;

foreach ($books as $book) {
    if (($book['status'] ?? 'active') !== 'active') continue;
    $slug = $book['slug'] ?? null;
    if (!$slug) continue;

    $tier = $book['tier'] ?? 'single';
    $price = (float)($tiers[$tier]['dkk'] ?? 0);
    $copy = $book['copy']['da'] ?? $book['copy']['en'] ?? [];
    $title = $copy['title'] ?? $slug;
    $subtitle = $copy['subtitle'] ?? ($copy['tagline'] ?? null);
    $descParts = $copy['description'] ?? [];
    $description = is_array($descParts) ? implode("\n\n", $descParts) : (string)$descParts;

    // PDF prefer EN then DA
    $pdfFilename = null;
    foreach (["{$slug}-en.pdf", "{$slug}-da.pdf", "{$slug}.pdf"] as $cand) {
        if (file_exists($pdfDir . '/' . $cand) || file_exists($jhRoot . '/storage/books/' . $cand)) {
            $src = file_exists($pdfDir . '/' . $cand) ? $pdfDir . '/' . $cand : $jhRoot . '/storage/books/' . $cand;
            if (!file_exists($pdfDir . '/' . $cand)) {
                @copy($src, $pdfDir . '/' . $cand);
            }
            $pdfFilename = $cand;
            break;
        }
    }

    // Cover
    $cover = null;
    foreach (["covers/{$slug}-da-640.webp", "covers/{$slug}-en-640.webp", "covers/{$slug}-da.webp", "covers/{$slug}-en.jpg", "covers/{$slug}.jpg"] as $rel) {
        $full = $uploadDir . '/img/' . $rel;
        if (file_exists($full)) {
            $cover = "uploads/{$tid}/img/{$rel}";
            break;
        }
    }

    $check = $pdo->prepare("SELECT id FROM ebooks WHERE tenant_id=? AND slug=?");
    $check->execute([$tid, $slug]);
    $existingId = $check->fetchColumn();

    if ($dryRun) {
        echo "  ebook $slug price=$price pdf=" . ($pdfFilename ?: '-') . "\n";
        $ebookCount++;
        continue;
    }

    if ($existingId) {
        $pdo->prepare("
            UPDATE ebooks SET title=?, subtitle=?, description=?, cover_image_path=COALESCE(?, cover_image_path),
            pdf_filename=COALESCE(?, pdf_filename), pdf_original_name=COALESCE(?, pdf_original_name),
            page_count=?, price_dkk=?, status='published', updated_at=NOW()
            WHERE id=?
        ")->execute([
            $title, $subtitle, $description, $cover, $pdfFilename, $pdfFilename,
            $book['pages'] ?? null, $price, $existingId,
        ]);
        echo "  UPDATED ebook $slug\n";
    } else {
        $pdo->prepare("
            INSERT INTO ebooks (tenant_id, slug, title, subtitle, description, cover_image_path,
                pdf_filename, pdf_original_name, page_count, price_dkk, status, created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?, 'published', NOW())
        ")->execute([
            $tid, $slug, $title, $subtitle, $description, $cover,
            $pdfFilename, $pdfFilename, $book['pages'] ?? null, $price,
        ]);
        echo "  INSERTED ebook $slug\n";
    }
    $ebookCount++;

    // Free tier → also lead magnet
    if ($price <= 0 && $pdfFilename) {
        $lmCheck = $pdo->prepare("SELECT id FROM lead_magnets WHERE tenant_id=? AND slug=?");
        $lmCheck->execute([$tid, $slug]);
        if (!$lmCheck->fetchColumn()) {
            $pdo->prepare("
                INSERT INTO lead_magnets (
                    tenant_id, slug, title, subtitle, hero_headline, hero_subheadline, hero_cta_text,
                    template, hero_bg_color, cover_image_path, pdf_filename, pdf_original_name,
                    email_subject, email_body_html, status, created_at
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'published', NOW())
            ")->execute([
                $tid, $slug, $title, $subtitle,
                $title,
                $subtitle ?: 'Gratis bog fra Jannik Hansen',
                'Hent gratis',
                'bold',
                '#0E0E10',
                $cover,
                $pdfFilename,
                $pdfFilename,
                'Din gratis bog: ' . $title,
                '<p>Tak! Din bog er klar til download.</p>',
            ]);
            echo "    + lead magnet $slug\n";
        }
    }
}
echo "Ebooks processed: $ebookCount\n";

// ---------- COURSES ----------
echo "\n--- Courses from JH DB ---\n";

$trackMeta = [
    'office-os' => [
        'title' => 'Office OS',
        'subtitle' => 'Halve your workday with AI',
        'price' => 2500,
        'description' => 'Master AI for emails, documents, spreadsheets and meetings. Keep your job, reclaim your time.',
    ],
    'creator-os' => [
        'title' => 'Creator OS',
        'subtitle' => 'Build inbound demand with AI',
        'price' => 9997,
        'description' => 'Turn your expertise into content that attracts opportunities. The AI content engine for creators.',
    ],
    'founder-os' => [
        'title' => 'Founder OS',
        'subtitle' => 'Own the whole business with AI',
        'price' => 14997,
        'description' => 'Build a one-person operation with AI handling ops, sales and delivery.',
    ],
];

foreach ($trackMeta as $slug => $meta) {
    $check = $pdo->prepare("SELECT id FROM courses WHERE tenant_id=? AND slug=?");
    $check->execute([$tid, $slug]);
    $courseId = $check->fetchColumn();

    if (!$courseId) {
        if ($dryRun) {
            echo "Would create course $slug\n";
            continue;
        }
        $pdo->prepare("
            INSERT INTO courses (
                tenant_id, slug, title, subtitle, description, short_description,
                pricing_type, price_dkk, status, is_featured, instructor_name, instructor_bio, created_at
            ) VALUES (?,?,?,?,?,?, 'one_time', ?, 'published', 1, 'Jannik Hansen', 'Strategic influence in the age of AI.', NOW())
        ")->execute([
            $tid, $slug, $meta['title'], $meta['subtitle'], $meta['description'], $meta['subtitle'], $meta['price'],
        ]);
        $courseId = (int)$pdo->lastInsertId();
        echo "Created course $slug id=$courseId\n";
    } else {
        $pdo->prepare("UPDATE courses SET price_dkk=?, status='published', pricing_type='one_time', title=?, subtitle=?, description=? WHERE id=?")
            ->execute([$meta['price'], $meta['title'], $meta['subtitle'], $meta['description'], $courseId]);
        echo "Updated course $slug id=$courseId\n";
        // Clear existing modules/lessons for clean reimport
        $modIds = $pdo->prepare("SELECT id FROM course_modules WHERE course_id=?");
        $modIds->execute([$courseId]);
        foreach ($modIds->fetchAll(PDO::FETCH_COLUMN) as $mid) {
            $pdo->prepare("DELETE FROM course_lessons WHERE module_id=?")->execute([$mid]);
        }
        $pdo->prepare("DELETE FROM course_modules WHERE course_id=?")->execute([$courseId]);
        echo "  cleared old modules/lessons\n";
    }

    // Modules from product_modules join modules
    $mods = $jhPdo->prepare("
        SELECT m.id, m.slug, m.title, m.description, m.position
        FROM product_modules pm
        JOIN modules m ON m.slug = pm.module_slug
        WHERE pm.product_slug = ? AND m.is_active = 1
        ORDER BY m.position ASC, m.id ASC
    ");
    $mods->execute([$slug]);
    $modules = $mods->fetchAll();
    $sort = 0;
    $lessonTotal = 0;

    foreach ($modules as $m) {
        if ($dryRun) {
            echo "  module {$m['slug']}\n";
            continue;
        }
        $pdo->prepare("
            INSERT INTO course_modules (course_id, tenant_id, title, description, sort_order)
            VALUES (?,?,?,?,?)
        ")->execute([$courseId, $tid, $m['title'], $m['description'], $sort++]);
        $moduleId = (int)$pdo->lastInsertId();

        $lessons = $jhPdo->prepare("
            SELECT id, title, description, content, video_filename, duration_seconds, order_index
            FROM lessons WHERE module_id = ? AND is_active = 1
            ORDER BY order_index ASC, id ASC
        ");
        $lessons->execute([$m['id']]);
        $lSort = 0;
        foreach ($lessons->fetchAll() as $l) {
            $hasVideo = !empty($l['video_filename']);
            $type = $hasVideo ? 'video_text' : 'text';
            $content = $l['content'] ?: ('<p>' . htmlspecialchars($l['description'] ?? $l['title']) . '</p>');
            // Convert plain text to basic HTML if no tags
            if ($content && strpos($content, '<') === false) {
                $content = '<p>' . nl2br(htmlspecialchars($content)) . '</p>';
            }
            $pdo->prepare("
                INSERT INTO course_lessons (
                    module_id, course_id, tenant_id, title, lesson_type, text_content,
                    video_duration_seconds, sort_order, is_preview
                ) VALUES (?,?,?,?,?,?,?,?,?)
            ")->execute([
                $moduleId, $courseId, $tid, $l['title'], $type, $content,
                $l['duration_seconds'] ?: null, $lSort++,
                $lSort === 1 ? 1 : 0,
            ]);
            $lessonTotal++;
        }
    }

    if (!$dryRun) {
        $pdo->prepare("UPDATE courses SET total_lessons=? WHERE id=?")->execute([$lessonTotal, $courseId]);
    }
    echo "  $slug: " . count($modules) . " modules, $lessonTotal lessons\n";
}

// ---------- EMAIL SEQUENCE (workshop placeholder) ----------
echo "\n--- Workshop email sequence ---\n";
$seqCheck = $pdo->prepare("SELECT id FROM email_sequences WHERE tenant_id=? AND name=?");
$seqCheck->execute([$tid, 'Workshop follow-up (Creator OS)']);
$seqId = $seqCheck->fetchColumn();
if (!$seqId && !$dryRun) {
    $pdo->prepare("
        INSERT INTO email_sequences (tenant_id, name, trigger_type, status, created_at)
        VALUES (?, 'Workshop follow-up (Creator OS)', 'manual', 'active', NOW())
    ")->execute([$tid]);
    $seqId = $pdo->lastInsertId();
    $steps = [
        [0, 'Tak for din tilmelding', '<p>Velkommen. Her er dit næste skridt i Creator OS rejsen.</p>'],
        [2, 'Mønsteret bag indhold der konverterer', '<p>De fleste poster. Få bygger et system. Her er forskellen.</p>'],
        [5, 'Din AI content stack', '<p>Tre værktøjer. Én workflow. Klar til at køre.</p>'],
        [8, 'Klar til Creator OS?', '<p>Når du er ready, er sporet her: <a href="/creator-os">Creator OS</a>.</p>'],
    ];
    foreach ($steps as $i => [$day, $subject, $body]) {
        $pdo->prepare("
            INSERT INTO email_sequence_steps (sequence_id, day_number, subject, body_html, sort_order)
            VALUES (?,?,?,?,?)
        ")->execute([$seqId, $day, $subject, $body, $i]);
    }
    echo "Created sequence id=$seqId with " . count($steps) . " steps\n";
} else {
    echo "Sequence exists or dry-run: $seqId\n";
}

// ---------- REDIRECTS ----------
echo "\n--- Redirects ---\n";
$redirects = [
    ['/creator-os/buy', '/course/creator-os'],
    ['/creator-os/buy-plan', '/course/creator-os'],
    ['/office-os/buy', '/course/office-os'],
    ['/office-os/buy-plan', '/course/office-os'],
    ['/founder-os/buy', '/course/founder-os'],
    ['/founder-os/buy-plan', '/course/founder-os'],
    ['/library', '/bibliotek'],
    ['/en/library', '/en-library'],
    ['/kontakt', '/contact'],
    ['/gratis', '/eboger'],
    ['/kurser', '/courses'],
];
foreach ($redirects as [$from, $to]) {
    $ex = $pdo->prepare("SELECT id FROM redirects WHERE tenant_id=? AND from_path=?");
    $ex->execute([$tid, $from]);
    if ($ex->fetchColumn()) continue;
    if ($dryRun) { echo "  redirect $from → $to\n"; continue; }
    $pdo->prepare("INSERT INTO redirects (tenant_id, from_path, to_path, status_code, is_active) VALUES (?,?,?,302,1)")
        ->execute([$tid, $from, $to]);
    echo "  + $from → $to\n";
}

// Point platform stripe keys onto tenant for buy flows if empty
if (!$dryRun) {
    $hasKey = $pdo->query("SELECT stripe_secret_key FROM tenants WHERE id=$tid")->fetchColumn();
    if (empty($hasKey) && !empty($_ENV['STRIPE_SECRET_KEY'])) {
        $pdo->prepare("UPDATE tenants SET stripe_secret_key=?, stripe_publishable_key=? WHERE id=?")->execute([
            $_ENV['STRIPE_SECRET_KEY'],
            $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '',
            $tid,
        ]);
        echo "Copied platform Stripe keys to tenant (for checkout)\n";
    }
}

echo "\n=== Phase 1 import complete ===\n";
echo "Ebooks: " . $pdo->query("SELECT COUNT(*) FROM ebooks WHERE tenant_id=$tid")->fetchColumn() . "\n";
echo "Courses: " . $pdo->query("SELECT COUNT(*) FROM courses WHERE tenant_id=$tid")->fetchColumn() . "\n";
echo "Lessons: " . $pdo->query("SELECT COUNT(*) FROM course_lessons WHERE tenant_id=$tid")->fetchColumn() . "\n";
echo "Lead magnets: " . $pdo->query("SELECT COUNT(*) FROM lead_magnets WHERE tenant_id=$tid")->fetchColumn() . "\n";
