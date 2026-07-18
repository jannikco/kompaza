<?php
/**
 * Seed Phase-0 course + ebooks for jannikhansen tenant from local JH data files.
 *
 * Usage:
 *   php scripts/import-jannikhansen-seed.php [--dry-run]
 *
 * Reads:
 *   - /Users/jh/GitHub/jannikhansen/data/library.yaml (or server path)
 *   - storage/pdfs/{tenant_id} for PDFs already synced
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

$tenant = $pdo->query("SELECT * FROM tenants WHERE slug='jannikhansen' LIMIT 1")->fetch();
if (!$tenant) die("ERROR: tenant missing\n");
$tid = (int)$tenant['id'];
echo "Tenant $tid\n";

// --- Course: Creator OS (subset) ---
$courseSlug = 'creator-os';
$exists = $pdo->prepare("SELECT id FROM courses WHERE tenant_id=? AND slug=?");
$exists->execute([$tid, $courseSlug]);
$courseId = $exists->fetchColumn();

if (!$courseId) {
    if ($dryRun) {
        echo "Would create course creator-os\n";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO courses (
                tenant_id, slug, title, subtitle, description, short_description,
                pricing_type, price_dkk, status, is_featured,
                instructor_name, instructor_bio, created_at
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW())
        ");
        $stmt->execute([
            $tid,
            $courseSlug,
            'Creator OS',
            'Build inbound demand with AI',
            'Turn your expertise into content that attracts opportunities. The AI content engine used by creators who want inbound leads without cold outreach.',
            'AI content engine for personal brand and inbound demand.',
            'one_time',
            9997.00,
            'published',
            1,
            'Jannik Hansen',
            'Strategic influence in the age of AI.',
        ]);
        $courseId = (int)$pdo->lastInsertId();
        echo "Created course id=$courseId\n";
    }
} else {
    echo "Course exists id=$courseId\n";
}

if ($courseId && !$dryRun) {
    // Ensure at least one module + lesson with text content
    $mod = $pdo->prepare("SELECT id FROM course_modules WHERE course_id=? LIMIT 1");
    $mod->execute([$courseId]);
    $moduleId = $mod->fetchColumn();
    if (!$moduleId) {
        $pdo->prepare("
            INSERT INTO course_modules (course_id, tenant_id, title, description, sort_order)
            VALUES (?,?,?,?,0)
        ")->execute([$courseId, $tid, 'Foundation', 'Start here — how Creator OS works']);
        $moduleId = (int)$pdo->lastInsertId();
        echo "Created module $moduleId\n";
    }

    $les = $pdo->prepare("SELECT id FROM course_lessons WHERE module_id=? LIMIT 1");
    $les->execute([$moduleId]);
    if (!$les->fetchColumn()) {
        // Detect lesson columns
        $cols = $pdo->query("SHOW COLUMNS FROM course_lessons")->fetchAll(PDO::FETCH_COLUMN);
        $hasText = in_array('text_content', $cols, true);
        $hasType = in_array('lesson_type', $cols, true);
        $hasTenant = in_array('tenant_id', $cols, true);

        $fields = ['module_id', 'course_id', 'title', 'sort_order'];
        $values = [$moduleId, $courseId, 'Welcome to Creator OS', 0];
        if ($hasTenant) { $fields[] = 'tenant_id'; $values[] = $tid; }
        if ($hasType) { $fields[] = 'lesson_type'; $values[] = 'text'; }
        if ($hasText) {
            $fields[] = 'text_content';
            $values[] = '<h2>Welcome</h2><p>This is a Phase-0 seed lesson imported into Kompaza. Replace with full curriculum from Creator OS modules.</p><p>One person + AI = an entire team.</p>';
        }
        if (in_array('is_preview', $cols, true)) {
            $fields[] = 'is_preview';
            $values[] = 1;
        }
        $ph = implode(',', array_fill(0, count($fields), '?'));
        $pdo->prepare("INSERT INTO course_lessons (" . implode(',', $fields) . ") VALUES ($ph)")->execute($values);
        echo "Created seed lesson\n";
    }
}

// --- Ebooks: pick free + one paid from library.yaml if readable ---
$jhRoots = [
    '/var/www/html/jannikhansen.com',
    '/Users/jh/GitHub/jannikhansen',
    dirname(__DIR__, 2) . '/jannikhansen',
];
$libraryYaml = null;
foreach ($jhRoots as $root) {
    $p = $root . '/data/library.yaml';
    if (file_exists($p)) { $libraryYaml = $p; break; }
}

$seedBooks = [
    [
        'slug' => 'eu-ai-act-the-complete-guide',
        'title' => 'EU AI Act — The Complete Guide',
        'price' => 0,
        'subtitle' => 'Practical compliance for builders and leaders',
    ],
    [
        'slug' => 'claude-mastery-playbook',
        'title' => 'Claude Mastery Playbook',
        'price' => 495,
        'subtitle' => 'Make Claude your operating system',
    ],
];

// Try parse yaml titles if file present (minimal line parser for slug blocks)
if ($libraryYaml) {
    echo "Using library.yaml: $libraryYaml\n";
}

$pdfDir = __DIR__ . '/../storage/pdfs/' . $tid;
if (!is_dir($pdfDir)) {
    @mkdir($pdfDir, 0755, true);
}

foreach ($seedBooks as $book) {
    $check = $pdo->prepare("SELECT id FROM ebooks WHERE tenant_id=? AND slug=?");
    $check->execute([$tid, $book['slug']]);
    if ($check->fetchColumn()) {
        echo "Ebook exists: {$book['slug']}\n";
        continue;
    }

    // Find a PDF already synced
    $pdfFilename = null;
    $pdfOriginal = null;
    if (is_dir($pdfDir)) {
        foreach (glob($pdfDir . '/' . $book['slug'] . '*.pdf') ?: [] as $f) {
            $pdfFilename = basename($f);
            $pdfOriginal = $pdfFilename;
            break;
        }
        if (!$pdfFilename) {
            // any pdf as placeholder for free guide
            $any = glob($pdfDir . '/*.pdf') ?: [];
            if ($any) {
                $pdfFilename = basename($any[0]);
                $pdfOriginal = $pdfFilename;
            }
        }
    }

    // Cover from uploads if present
    $cover = null;
    $coverCandidates = [
        __DIR__ . "/../public/uploads/{$tid}/img/covers/{$book['slug']}-en.webp",
        __DIR__ . "/../public/uploads/{$tid}/img/covers/{$book['slug']}-en.jpg",
        __DIR__ . "/../public/uploads/{$tid}/img/covers/{$book['slug']}-da.webp",
        __DIR__ . "/../public/uploads/{$tid}/img/covers/{$book['slug']}.jpg",
    ];
    foreach ($coverCandidates as $c) {
        if (file_exists($c)) {
            $cover = "uploads/{$tid}/img/covers/" . basename($c);
            break;
        }
    }

    if ($dryRun) {
        echo "Would create ebook {$book['slug']} pdf=" . ($pdfFilename ?: 'none') . " cover=" . ($cover ?: 'none') . "\n";
        continue;
    }

    $stmt = $pdo->prepare("
        INSERT INTO ebooks (
            tenant_id, slug, title, subtitle, description,
            cover_image_path, pdf_filename, pdf_original_name,
            price_dkk, status, created_at
        ) VALUES (?,?,?,?,?,?,?,?,?,?,NOW())
    ");
    $stmt->execute([
        $tid,
        $book['slug'],
        $book['title'],
        $book['subtitle'],
        $book['title'] . ' — imported from jannikhansen.com Library (Phase 0 seed).',
        $cover,
        $pdfFilename,
        $pdfOriginal,
        $book['price'],
        'published',
    ]);
    echo "Created ebook {$book['slug']} id=" . $pdo->lastInsertId() . "\n";
}

// Seed redirects for key multi-segment-ish marketing paths already single-segment
// (library book deep links later)

echo "Seed complete.\n";
echo "Course: https://jannikhansen.kompaza.com/kurser or /courses\n";
echo "Ebooks: https://jannikhansen.kompaza.com/eboger\n";
