<?php
/**
 * Fill remaining JH parity gaps (no DNS):
 * - everything-bundle ebook
 * - full workshop nurture sequences (6 × 12 steps)
 * - map module S3 videos onto course lessons
 * - import remaining EN marketing pages if missing
 * - sync S3 credentials note
 *
 * php scripts/import-jannikhansen-gaps.php
 */

$envFile = __DIR__ . '/../.env';
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
    [$k, $v] = explode('=', $line, 2);
    $_ENV[trim($k)] = trim($v, " \t\"'");
}
require_once __DIR__ . '/../src/Config/jh-prices.php';

$pdo = new PDO(
    sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $_ENV['DB_HOST'] ?? 'localhost', $_ENV['DB_PORT'] ?? '3306', $_ENV['DB_DATABASE'] ?? 'kompaza'),
    $_ENV['DB_USERNAME'] ?? 'root', $_ENV['DB_PASSWORD'] ?? '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

$jhPdo = null;
foreach ([
    ['mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=jannikhansen;charset=utf8mb4', 'root', ''],
    ['mysql:host=127.0.0.1;dbname=jannikhansen;charset=utf8mb4', 'root', ''],
] as [$dsn, $u, $p]) {
    try { $jhPdo = new PDO($dsn, $u, $p, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]); break; }
    catch (Exception $e) {}
}
if (!$jhPdo) die("Need JH DB access\n");

$tid = (int)$pdo->query("SELECT id FROM tenants WHERE slug='jannikhansen'")->fetchColumn();
if (!$tid) die("tenant missing\n");
echo "Tenant $tid\n";

$jhRoot = '/var/www/html/jannikhansen.com';

// ---- everything-bundle ----
echo "--- everything-bundle ---\n";
$check = $pdo->prepare("SELECT id FROM ebooks WHERE tenant_id=? AND slug='everything-bundle'");
$check->execute([$tid]);
if (!$check->fetchColumn()) {
    $cover = null;
    foreach (["everything-bundle-da-640.webp", "everything-bundle-en-640.webp"] as $c) {
        if (file_exists(__DIR__ . "/../public/uploads/{$tid}/img/covers/{$c}")) {
            $cover = "uploads/{$tid}/img/covers/{$c}";
            break;
        }
    }
    $pdo->prepare("
        INSERT INTO ebooks (tenant_id, slug, title, subtitle, description, cover_image_path, price_dkk, status, created_at)
        VALUES (?,?,?,?,?,?,?, 'published', NOW())
    ")->execute([
        $tid,
        'everything-bundle',
        'Hele Biblioteket / The Complete Library',
        'Alle 34 bøger — dansk + engelsk',
        'Ét køb. Hele systemet. All 34 books in one purchase.',
        $cover,
        2495.00,
    ]);
    echo "Inserted everything-bundle\n";
} else {
    $pdo->prepare("UPDATE ebooks SET price_dkk=2495, status='published', title='Hele Biblioteket / The Complete Library' WHERE tenant_id=? AND slug='everything-bundle'")
        ->execute([$tid]);
    echo "Updated everything-bundle\n";
}

// ---- Workshop nurture sequences ----
echo "--- Workshop nurture sequences ---\n";
$seqPath = $jhRoot . '/emails/nurture/sequences.json';
$layoutPath = $jhRoot . '/emails/nurture/_layout.html';
$sequences = json_decode(file_get_contents($seqPath), true) ?: [];
$layout = file_exists($layoutPath) ? file_get_contents($layoutPath) : '{{body}}';

// Day offsets for 12 steps (~3 weeks)
$dayOffsets = [0, 1, 2, 3, 5, 7, 9, 11, 14, 17, 20, 24];

foreach ($sequences as $key => $steps) {
    if (!is_array($steps) || !str_starts_with($key, 'workshop-')) continue;
    // key: workshop-creator-os-da
    $parts = explode('-', $key);
    // workshop, office|creator|founder, os, da|en  OR workshop-office-os-da
    if (preg_match('/^workshop-(office-os|creator-os|founder-os)-(da|en)$/', $key, $m)) {
        $track = $m[1];
        $locale = $m[2];
    } else {
        continue;
    }
    $name = 'Workshop nurture: ' . $track . ' (' . $locale . ')';
    $ex = $pdo->prepare("SELECT id FROM email_sequences WHERE tenant_id=? AND name=?");
    $ex->execute([$tid, $name]);
    $seqId = $ex->fetchColumn();
    if ($seqId) {
        $pdo->prepare("DELETE FROM email_sequence_steps WHERE sequence_id=?")->execute([$seqId]);
        $pdo->prepare("UPDATE email_sequences SET status='active' WHERE id=?")->execute([$seqId]);
    } else {
        $pdo->prepare("INSERT INTO email_sequences (tenant_id, name, trigger_type, status, created_at) VALUES (?,?, 'manual', 'active', NOW())")
            ->execute([$tid, $name]);
        $seqId = $pdo->lastInsertId();
    }

    foreach ($steps as $i => $step) {
        $subject = $step['subject'] ?? ('Step ' . ($i + 1));
        $bodyInner = $step['body_html'] ?? '';
        // wrap in layout if placeholder exists
        $body = str_contains($layout, '{{body}}')
            ? str_replace('{{body}}', $bodyInner, $layout)
            : $bodyInner;
        // crude merge tags
        $price = jhTrackAmount($track, 'full', $locale === 'en' ? 'eur' : 'dkk');
        $body = str_replace('{{price}}', number_format($price, 0, ',', '.') . ' ' . strtoupper($locale === 'en' ? 'EUR' : 'DKK'), $body);
        $day = $dayOffsets[$i] ?? $i;
        $pdo->prepare("
            INSERT INTO email_sequence_steps (sequence_id, day_number, subject, body_html, sort_order)
            VALUES (?,?,?,?,?)
        ")->execute([$seqId, $day, $subject, $body, $i]);
    }
    echo "  $name — " . count($steps) . " steps (id=$seqId)\n";
}

// ---- Import legacy video modules (OS tracks are text; videos live on legacy courses) ----
echo "--- Import legacy S3 video courses ---\n";
$mods = $jhPdo->query("SELECT id,slug,title,description,s3_object_key,duration_seconds FROM modules WHERE s3_object_key IS NOT NULL AND s3_object_key!='' ORDER BY id")->fetchAll();
$groups = [];
foreach ($mods as $m) {
    if (preg_match('#legacy/videos/course_(\d+)/#', $m['s3_object_key'], $mm)) {
        $g = 'course_' . $mm[1];
    } else {
        $g = 'other';
    }
    $groups[$g][] = $m;
}
$mapNames = [
    'course_1' => ['slug' => 'eu-ai-act-video', 'title' => 'EU AI Act (video)'],
    'course_6' => ['slug' => 'claude-cowork-video', 'title' => 'Claude Cowork Masterclass (video)'],
    'course_26' => ['slug' => 'claude-i-office-video', 'title' => 'Claude i Office (video)'],
    'other' => ['slug' => 'extra-videos', 'title' => 'Additional Videos'],
];
$videoMapped = 0;
foreach ($groups as $g => $list) {
    $meta = $mapNames[$g] ?? ['slug' => $g, 'title' => $g];
    $ex = $pdo->prepare("SELECT id FROM courses WHERE tenant_id=? AND slug=?");
    $ex->execute([$tid, $meta['slug']]);
    $cid = $ex->fetchColumn();
    if ($cid) {
        $mids = $pdo->prepare("SELECT id FROM course_modules WHERE course_id=?");
        $mids->execute([$cid]);
        foreach ($mids->fetchAll(PDO::FETCH_COLUMN) as $mid) {
            $pdo->prepare("DELETE FROM course_lessons WHERE module_id=?")->execute([$mid]);
        }
        $pdo->prepare("DELETE FROM course_modules WHERE course_id=?")->execute([$cid]);
    } else {
        $pdo->prepare("INSERT INTO courses (tenant_id,slug,title,subtitle,description,pricing_type,price_dkk,status,instructor_name,created_at) VALUES (?,?,?,?,?,\"free\",0,\"published\",\"Jannik Hansen\",NOW())")
            ->execute([$tid, $meta['slug'], $meta['title'], 'Video lessons from jannikhansen', 'Video curriculum']);
        $cid = (int)$pdo->lastInsertId();
    }
    $pdo->prepare("INSERT INTO course_modules (course_id,tenant_id,title,description,sort_order) VALUES (?,?,?,?,0)")
        ->execute([$cid, $tid, $meta['title'], 'Imported video lessons']);
    $moduleId = (int)$pdo->lastInsertId();
    $i = 0;
    foreach ($list as $m) {
        $pdo->prepare("INSERT INTO course_lessons (module_id,course_id,tenant_id,title,lesson_type,text_content,video_s3_key,video_status,video_duration_seconds,sort_order,is_preview) VALUES (?,?,?,?,\"video\",?,?,\"ready\",?,?,?)")
            ->execute([
                $moduleId, $cid, $tid, $m['title'],
                $m['description'] ? '<p>' . nl2br(htmlspecialchars($m['description'])) . '</p>' : null,
                $m['s3_object_key'], $m['duration_seconds'], $i, $i === 0 ? 1 : 0,
            ]);
        $i++;
        $videoMapped++;
    }
    $pdo->prepare("UPDATE courses SET total_lessons=? WHERE id=?")->execute([$i, $cid]);
    echo "  {$meta['slug']}: $i video lessons\n";
}
echo "Mapped $videoMapped video lessons\n";

// ---- EN locale sticky helper page note ----
echo "--- EN route redirects ---\n";
$enRedirects = [
    ['/en', '/en-home'],
    ['/en/', '/en-home'],
    ['/en/tracks', '/en-tracks'],
    ['/en/creator-os', '/en-creator-os'],
    ['/en/office-os', '/office-os'],
    ['/en/founder-os', '/founder-os'],
    ['/en/library', '/en-library'],
    ['/en/about', '/en-about'],
    ['/en/workshop', '/workshop'],
    ['/en/faq', '/faq'],
    ['/en/contact', '/contact'],
    ['/en/privacy', '/privacy'],
    ['/en/terms', '/terms'],
];
foreach ($enRedirects as [$from, $to]) {
    $ex = $pdo->prepare("SELECT id FROM redirects WHERE tenant_id=? AND from_path=?");
    $ex->execute([$tid, $from]);
    if ($ex->fetchColumn()) continue;
    $pdo->prepare("INSERT INTO redirects (tenant_id, from_path, to_path, status_code, is_active) VALUES (?,?,?,302,1)")
        ->execute([$tid, $from, $to]);
    echo "  + $from → $to\n";
}

// ---- Currency switcher cookie endpoint note (handled in router) ----
echo "Done gaps import.\n";
echo "Sequences: " . $pdo->query("SELECT COUNT(*) FROM email_sequences WHERE tenant_id=$tid")->fetchColumn() . "\n";
echo "Steps: " . $pdo->query("SELECT COUNT(*) FROM email_sequence_steps s JOIN email_sequences e ON e.id=s.sequence_id WHERE e.tenant_id=$tid")->fetchColumn() . "\n";
echo "Videos ready: " . $pdo->query("SELECT COUNT(*) FROM course_lessons WHERE tenant_id=$tid AND video_status='ready'")->fetchColumn() . "\n";
