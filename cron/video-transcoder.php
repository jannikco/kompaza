<?php
/**
 * Video Transcoder Cron Job
 *
 * Run every minute via cron:
 * * * * * * php /var/www/kompaza.com/cron/video-transcoder.php >> /var/www/kompaza.com/storage/logs/transcoder.log 2>&1
 *
 * Requires: ffmpeg, ffprobe
 */

require_once __DIR__ . '/../src/Config/config.php';

use App\Database\Database;
use App\Models\CourseLesson;
use App\Models\Course;
use App\Services\S3Service;

// Prevent overlapping runs
$lockFile = STORAGE_PATH . '/transcoder.lock';
if (file_exists($lockFile)) {
    $lockTime = (int)file_get_contents($lockFile);
    // Allow stale locks (older than 30 minutes) to be overridden
    if (time() - $lockTime < 1800) {
        echo date('Y-m-d H:i:s') . " Transcoder already running, skipping.\n";
        exit;
    }
}
file_put_contents($lockFile, time());

register_shutdown_function(function() use ($lockFile) {
    @unlink($lockFile);
});

$db = Database::getConnection();

// Pick oldest pending job
$stmt = $db->prepare("SELECT * FROM video_transcode_jobs WHERE status = 'pending' ORDER BY created_at ASC LIMIT 1");
$stmt->execute();
$job = $stmt->fetch();

if (!$job) {
    echo date('Y-m-d H:i:s') . " No pending jobs.\n";
    exit;
}

$jobId = $job['id'];
$lessonId = $job['lesson_id'];
$tenantId = $job['tenant_id'];
$sourcePath = $job['source_local_path'];

echo date('Y-m-d H:i:s') . " Processing job #$jobId for lesson #$lessonId\n";

// Mark as processing
$stmt = $db->prepare("UPDATE video_transcode_jobs SET status = 'processing', started_at = NOW() WHERE id = ?");
$stmt->execute([$jobId]);
CourseLesson::update($lessonId, ['video_status' => 'transcoding']);

try {
    // Verify source file exists
    if (!file_exists($sourcePath)) {
        throw new \Exception("Source file not found: $sourcePath");
    }

    // Get duration via ffprobe
    $durationCmd = "ffprobe -v quiet -show_entries format=duration -of csv='p=0' " . escapeshellarg($sourcePath) . " 2>/dev/null";
    $duration = (int)round((float)trim(shell_exec($durationCmd)));

    // Prepare output paths
    $outputDir = STORAGE_PATH . '/videos/' . $tenantId . '/transcoded';
    if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);

    $outputFilename = 'lesson_' . $lessonId . '_720p_' . time() . '.mp4';
    $outputPath = $outputDir . '/' . $outputFilename;

    $thumbnailFilename = 'lesson_' . $lessonId . '_thumb_' . time() . '.jpg';
    $thumbnailPath = $outputDir . '/' . $thumbnailFilename;

    // Transcode to 720p H.264 + AAC
    $ffmpegCmd = sprintf(
        'ffmpeg -i %s -vf "scale=-2:720" -c:v libx264 -crf 23 -preset medium -c:a aac -b:a 128k -movflags +faststart -y %s 2>&1',
        escapeshellarg($sourcePath),
        escapeshellarg($outputPath)
    );

    echo date('Y-m-d H:i:s') . " Running ffmpeg...\n";
    $output = [];
    $returnCode = 0;
    exec($ffmpegCmd, $output, $returnCode);

    if ($returnCode !== 0) {
        throw new \Exception("ffmpeg failed (code $returnCode): " . implode("\n", array_slice($output, -5)));
    }

    // Extract thumbnail at 10 seconds (or 1 second if video is shorter)
    $thumbTime = min(10, max(1, $duration - 1));
    $thumbCmd = sprintf(
        'ffmpeg -i %s -ss %d -vframes 1 -q:v 2 -y %s 2>&1',
        escapeshellarg($outputPath),
        $thumbTime,
        escapeshellarg($thumbnailPath)
    );
    exec($thumbCmd);

    // Upload to S3
    if (S3Service::isConfigured()) {
        echo date('Y-m-d H:i:s') . " Uploading to S3...\n";
        $stmt = $db->prepare("UPDATE video_transcode_jobs SET status = 'uploading' WHERE id = ?");
        $stmt->execute([$jobId]);

        $s3 = new S3Service();
        $lesson = CourseLesson::find($lessonId);
        $courseId = $lesson['course_id'];

        $videoS3Key = $s3->getKeyForCourseVideo($tenantId, $courseId, $lessonId, $outputFilename);
        $thumbnailS3Key = $s3->getKeyForCourseThumbnail($tenantId, $courseId, $lessonId, $thumbnailFilename);

        $uploaded = $s3->putObjectStream($videoS3Key, $outputPath, 'video/mp4');
        if (!$uploaded) {
            throw new \Exception("Failed to upload video to S3");
        }

        // Upload thumbnail
        if (file_exists($thumbnailPath)) {
            $s3->putObject($thumbnailS3Key, $thumbnailPath, 'image/jpeg');
        }

        // Update lesson with S3 keys
        CourseLesson::update($lessonId, [
            'video_s3_key' => $videoS3Key,
            'video_thumbnail_s3_key' => $thumbnailS3Key,
            'video_duration_seconds' => $duration,
            'video_status' => 'ready',
            'video_error_message' => null,
        ]);

        // Update job
        $stmt = $db->prepare("UPDATE video_transcode_jobs SET status = 'completed', output_s3_key = ?, thumbnail_s3_key = ?, duration_seconds = ?, completed_at = NOW() WHERE id = ?");
        $stmt->execute([$videoS3Key, $thumbnailS3Key, $duration, $jobId]);

        // Clean up local files
        @unlink($outputPath);
        @unlink($thumbnailPath);
        @unlink($sourcePath);

    } else {
        // No S3 configured — keep files locally, still mark as ready
        CourseLesson::update($lessonId, [
            'video_duration_seconds' => $duration,
            'video_status' => 'ready',
            'video_error_message' => 'S3 not configured - file kept locally',
        ]);

        $stmt = $db->prepare("UPDATE video_transcode_jobs SET status = 'completed', duration_seconds = ?, completed_at = NOW() WHERE id = ?");
        $stmt->execute([$duration, $jobId]);

        @unlink($sourcePath);
    }

    // Recalculate course stats
    $lesson = CourseLesson::find($lessonId);
    if ($lesson) {
        Course::recalculateStats($lesson['course_id']);
    }

    echo date('Y-m-d H:i:s') . " Job #$jobId completed successfully.\n";

} catch (\Exception $e) {
    echo date('Y-m-d H:i:s') . " Job #$jobId failed: " . $e->getMessage() . "\n";

    $stmt = $db->prepare("UPDATE video_transcode_jobs SET status = 'failed', error_message = ?, completed_at = NOW() WHERE id = ?");
    $stmt->execute([$e->getMessage(), $jobId]);

    CourseLesson::update($lessonId, [
        'video_status' => 'failed',
        'video_error_message' => $e->getMessage(),
    ]);
}
