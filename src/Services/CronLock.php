<?php

namespace App\Services;

/**
 * Single-instance guard for cron scripts.
 *
 * Uses flock(LOCK_EX|LOCK_NB) on a lock file, which the kernel releases
 * automatically if the process dies — unlike the file_exists() + stale-timeout
 * approach in cron/video-transcoder.php, which leaves a lock behind on a fatal
 * error and needs an arbitrary staleness window to recover.
 *
 * This matters most for the LinkedIn runners. connectpilot-post-runner.php is
 * scheduled every 5 minutes but sleeps 2-8 seconds between every reply, between
 * every DM, and again inside every LinkedInService call, across all active
 * automations. It routinely overruns its window, so without a lock a second copy
 * starts while the first is mid-flight and the same person is messaged twice.
 *
 * Usage, as the first thing a cron does after loading config:
 *
 *     $lock = CronLock::acquire('connectpilot-post-runner');
 *     if (!$lock) { echo "Already running; exiting.\n"; exit(0); }
 *
 * The handle must stay in scope for the lifetime of the script — releasing it is
 * what frees the lock. Let it fall out of scope at exit, or call release().
 */
final class CronLock
{
    /** @var resource|null */
    private $handle;
    private string $path;
    private bool $released = false;

    private function __construct($handle, string $path)
    {
        $this->handle = $handle;
        $this->path   = $path;
    }

    /**
     * Try to take the named lock.
     *
     * @return self|null null when another process already holds it
     */
    public static function acquire(string $name): ?self
    {
        $dir = STORAGE_PATH . '/locks';
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            error_log("CronLock: cannot create {$dir}; running without a lock.");
            return null;
        }

        $path   = $dir . '/' . preg_replace('/[^a-z0-9._-]/i', '_', $name) . '.lock';
        $handle = @fopen($path, 'c');
        if ($handle === false) {
            error_log("CronLock: cannot open {$path}; running without a lock.");
            return null;
        }

        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            return null;
        }

        // Record the owner for diagnosis; the lock itself is the flock, not this.
        ftruncate($handle, 0);
        fwrite($handle, getmypid() . ' ' . date('c') . "\n");
        fflush($handle);

        return new self($handle, $path);
    }

    /** Release early. Otherwise the lock frees when the handle closes at exit. */
    public function release(): void
    {
        if ($this->released || !is_resource($this->handle)) {
            return;
        }
        flock($this->handle, LOCK_UN);
        fclose($this->handle);
        $this->released = true;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function __destruct()
    {
        $this->release();
    }
}
