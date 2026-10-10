#!/usr/bin/env php
<?php
/**
 * Minimal test runner for the Outreach subsystem.
 *
 * There is no PHPUnit in this project and adding one for a handful of pure
 * functions would be a heavier dependency than the thing it tests. This runs every
 * `*.test.php` in scripts/tests/ and reports pass/fail.
 *
 *   php scripts/outreach-test.php              # run everything
 *   php scripts/outreach-test.php CommentParser # run matching files only
 *
 * A test file gets two helpers, `ok()` and `section()`, and is expected to be pure:
 * no database, no network, no config. That constraint is the point — it is what
 * keeps these runnable on any machine, in CI, and without a LinkedIn session.
 *
 * Exit code is non-zero when anything fails, so this can gate a deploy.
 */

$root = dirname(__DIR__);

$GLOBALS['__t'] = ['pass' => 0, 'fail' => 0, 'failures' => []];

function section(string $name): void
{
    echo "\n  {$name}\n";
}

function ok(string $label, bool $condition, string $got = ''): void
{
    if ($condition) {
        $GLOBALS['__t']['pass']++;
        echo "    ok    {$label}\n";
        return;
    }
    $GLOBALS['__t']['fail']++;
    $GLOBALS['__t']['failures'][] = $label;
    echo "    FAIL  {$label}" . ($got !== '' ? "  (got: {$got})" : '') . "\n";
}

$filter = $argv[1] ?? null;
$files  = glob($root . '/scripts/tests/*.test.php') ?: [];

if ($filter !== null) {
    $files = array_values(array_filter(
        $files,
        static fn ($f) => stripos(basename($f), $filter) !== false
    ));
}

if ($files === []) {
    fwrite(STDERR, "No test files matched.\n");
    exit(1);
}

foreach ($files as $file) {
    echo "\n" . basename($file, '.test.php') . "\n";
    require $file;
}

$t = $GLOBALS['__t'];
echo "\n" . str_repeat('-', 56) . "\n";
printf("  %d passed, %d failed\n", $t['pass'], $t['fail']);
if ($t['fail'] > 0) {
    echo "\n  Failures:\n";
    foreach ($t['failures'] as $f) {
        echo "    - {$f}\n";
    }
}
echo "\n";

exit($t['fail'] > 0 ? 1 : 0);
