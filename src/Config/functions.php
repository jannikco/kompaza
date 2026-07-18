<?php

use App\Services\TenantResolver;

function redirect($url, $statusCode = 302) {
    header("Location: $url", true, $statusCode);
    exit();
}

function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function isGet() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateCsrfToken() {
    if (isset($_COOKIE[CSRF_TOKEN_NAME])) {
        return $_COOKIE[CSRF_TOKEN_NAME];
    }
    $token = bin2hex(random_bytes(32));
    // SameSite=Lax reduces CSRF risk for cross-site POSTs while keeping double-submit usable.
    if (PHP_VERSION_ID >= 70300) {
        setcookie(CSRF_TOKEN_NAME, $token, [
            'expires' => time() + (180 * 24 * 60 * 60),
            'path' => '/',
            'secure' => true,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    } else {
        setcookie(CSRF_TOKEN_NAME, $token, time() + (180 * 24 * 60 * 60), '/; samesite=Lax', '', true, false);
    }
    $_COOKIE[CSRF_TOKEN_NAME] = $token;
    return $token;
}

function verifyCsrfToken($token) {
    return isset($_COOKIE[CSRF_TOKEN_NAME]) && hash_equals($_COOKIE[CSRF_TOKEN_NAME], $token);
}

function csrfField() {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generateCsrfToken() . '">';
}

function isAuthenticated() {
    return \App\Auth\Auth::check();
}

function currentUser() {
    return \App\Auth\Auth::user();
}

function currentUserId() {
    return \App\Auth\Auth::id();
}

function currentUserRole() {
    return \App\Auth\Auth::role();
}

function isSuperAdmin() {
    return \App\Auth\Auth::isSuperAdmin();
}

function isTenantAdmin() {
    return \App\Auth\Auth::isTenantAdmin();
}

function isCustomer() {
    return \App\Auth\Auth::isCustomer();
}

function currentTenant() {
    return TenantResolver::current();
}

function currentTenantId() {
    $tenant = TenantResolver::current();
    return $tenant['id'] ?? null;
}

function tenantSetting($key, $default = null) {
    $tenantId = currentTenantId();
    if (!$tenantId) return $default;
    return \App\Models\Setting::get($key, $tenantId, $default);
}

function dd($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    die();
}

function generateUuid() {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function generateUniqueId($prefix = '') {
    return $prefix . uniqid() . bin2hex(random_bytes(4));
}

function getClientIp() {
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER)) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = explode(',', $ip)[0];
            }
            if (filter_var(trim($ip), FILTER_VALIDATE_IP) !== false) {
                return trim($ip);
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function getUserAgent() {
    return substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
}

function checkRateLimit($identifier, $action, $maxAttempts, $timeWindow = 3600) {
    $db = \App\Database\Database::getConnection();

    $stmt = $db->prepare("DELETE FROM rate_limits WHERE last_attempt < DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->execute([$timeWindow]);

    $stmt = $db->prepare("
        SELECT attempts, UNIX_TIMESTAMP(last_attempt) as last_attempt
        FROM rate_limits
        WHERE identifier = ? AND action = ?
    ");
    $stmt->execute([$identifier, $action]);
    $limit = $stmt->fetch();

    if ($limit) {
        $timePassed = time() - $limit['last_attempt'];
        if ($timePassed < $timeWindow && $limit['attempts'] >= $maxAttempts) {
            return false;
        }
        if ($timePassed >= $timeWindow) {
            $stmt = $db->prepare("UPDATE rate_limits SET attempts = 1, last_attempt = NOW() WHERE identifier = ? AND action = ?");
            $stmt->execute([$identifier, $action]);
        } else {
            $stmt = $db->prepare("UPDATE rate_limits SET attempts = attempts + 1, last_attempt = NOW() WHERE identifier = ? AND action = ?");
            $stmt->execute([$identifier, $action]);
        }
    } else {
        $stmt = $db->prepare("INSERT INTO rate_limits (identifier, action, attempts, last_attempt) VALUES (?, ?, 1, NOW())");
        $stmt->execute([$identifier, $action]);
    }
    return true;
}

function logAudit($action, $entityType = null, $entityId = null, $payload = null) {
    try {
        $db = \App\Database\Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO audit_logs (tenant_id, user_id, action, entity_type, entity_id, payload, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            currentTenantId(),
            currentUserId(),
            $action,
            $entityType,
            $entityId,
            $payload ? json_encode($payload) : null,
            getClientIp(),
            getUserAgent()
        ]);
    } catch (Exception $e) {
        if (APP_DEBUG) {
            error_log("Audit log failed: " . $e->getMessage());
        }
    }
}

function view($template, $data = []) {
    extract($data);
    $templatePath = VIEWS_PATH . '/' . $template . '.php';
    if (!file_exists($templatePath)) {
        die("View not found: $template");
    }
    require $templatePath;
}

function asset($path) {
    return '/' . ltrim($path, '/');
}

function url($path = '') {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? PLATFORM_DOMAIN;
    return $scheme . '://' . $host . '/' . ltrim($path, '/');
}

function tenantUrl($path = '', $tenant = null) {
    $tenant = $tenant ?? currentTenant();
    if (!$tenant) return url($path);
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    if ($tenant['custom_domain']) {
        return $scheme . '://' . $tenant['custom_domain'] . '/' . ltrim($path, '/');
    }
    return $scheme . '://' . $tenant['slug'] . '.' . PLATFORM_DOMAIN . '/' . ltrim($path, '/');
}

function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function slugify($text) {
    $text = mb_strtolower($text, 'UTF-8');
    $text = str_replace(['æ', 'ø', 'å', 'ä', 'ö', 'ü'], ['ae', 'oe', 'aa', 'ae', 'oe', 'ue'], $text);
    $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

function formatDate($date, $format = 'd M Y') {
    if (!$date) return '';
    return date($format, strtotime($date));
}

function formatMoney($amount, $currency = 'DKK') {
    return number_format((float)$amount, 2, ',', '.') . ' ' . $currency;
}

function flashMessage($type, $message) {
    setcookie('kz_flash', json_encode(['type' => $type, 'message' => $message]), time() + 60, '/', '', true, true);
}

function getFlashMessage() {
    if (isset($_COOKIE['kz_flash'])) {
        $flash = json_decode($_COOKIE['kz_flash'], true);
        setcookie('kz_flash', '', time() - 3600, '/', '', true, true);
        return $flash;
    }
    return null;
}

function truncate($text, $length = 150, $suffix = '...') {
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . $suffix;
}

function tenantUploadPath($subdir = '') {
    $tenantId = currentTenantId();
    $path = PUBLIC_PATH . '/uploads/' . $tenantId;
    if ($subdir) $path .= '/' . ltrim($subdir, '/');
    if (!is_dir($path)) mkdir($path, 0755, true);
    return $path;
}

function tenantStoragePath($subdir = '') {
    $tenantId = currentTenantId();
    $path = STORAGE_PATH . '/pdfs/' . $tenantId;
    if ($subdir) $path .= '/' . ltrim($subdir, '/');
    if (!is_dir($path)) mkdir($path, 0755, true);
    return $path;
}

function uploadPublicFile($tmpFile, $category, $prefix, $ext) {
    $tenantId = currentTenantId();
    $filename = generateUniqueId($prefix . '_') . '.' . $ext;

    if (\App\Services\S3Service::isConfigured()) {
        $key = "tenants/{$tenantId}/{$category}/{$filename}";
        $contentType = mime_content_type($tmpFile) ?: 'application/octet-stream';
        $s3 = new \App\Services\S3Service();
        if ($s3->putObject($key, $tmpFile, $contentType)) {
            return $key;
        }
    }

    // Fallback to local upload
    $uploadPath = tenantUploadPath($category);
    move_uploaded_file($tmpFile, $uploadPath . '/' . $filename);
    return '/uploads/' . $tenantId . '/' . $category . '/' . $filename;
}

function imageUrl($pathOrKey, $expiresInSeconds = 604800) {
    if (!$pathOrKey) return '';
    // Local public uploads — serve as-is (with or without leading slash)
    if (str_starts_with($pathOrKey, '/uploads/')) return $pathOrKey;
    if (str_starts_with($pathOrKey, 'uploads/')) return '/' . $pathOrKey;
    // Already a full URL (legacy) — return as-is
    if (str_starts_with($pathOrKey, 'http://') || str_starts_with($pathOrKey, 'https://')) return $pathOrKey;
    // S3 key — generate presigned URL (7-day default)
    if (\App\Services\S3Service::isConfigured()) {
        $s3 = new \App\Services\S3Service();
        return $s3->getPresignedUrl($pathOrKey, $expiresInSeconds);
    }
    // Last-resort local relative
    return '/' . ltrim($pathOrKey, '/');
}

function uploadPrivateFile($tmpFile, $category, $prefix, $ext) {
    $tenantId = currentTenantId();
    $filename = generateUniqueId($prefix . '_') . '.' . $ext;

    if (\App\Services\S3Service::isConfigured()) {
        $key = "tenants/{$tenantId}/private/{$category}/{$filename}";
        $contentType = mime_content_type($tmpFile) ?: 'application/octet-stream';
        $s3 = new \App\Services\S3Service();
        if ($s3->putObject($key, $tmpFile, $contentType)) {
            return $key;
        }
    }

    // Fallback to local upload
    $storagePath = tenantStoragePath();
    move_uploaded_file($tmpFile, $storagePath . '/' . $filename);
    return $filename;
}

function deleteUploadedFile($pathOrKey) {
    if (!$pathOrKey) return;

    if (str_starts_with($pathOrKey, 'http')) {
        // S3 URL — extract key from public domain URL
        $publicDomain = S3_PUBLIC_DOMAIN;
        $pos = strpos($pathOrKey, $publicDomain);
        if ($pos !== false) {
            $key = ltrim(substr($pathOrKey, $pos + strlen($publicDomain)), '/');
            if (\App\Services\S3Service::isConfigured()) {
                $s3 = new \App\Services\S3Service();
                $s3->deleteObject($key);
            }
        }
    } elseif (str_starts_with($pathOrKey, '/uploads/')) {
        // Local public file
        $localPath = PUBLIC_PATH . $pathOrKey;
        if (file_exists($localPath)) unlink($localPath);
    } else {
        // S3 key (private files)
        if (\App\Services\S3Service::isConfigured()) {
            $s3 = new \App\Services\S3Service();
            $s3->deleteObject($pathOrKey);
        }
        // Also try local path for backward compat (plain filename = local PDF)
        $tenantId = currentTenantId();
        $localPath = STORAGE_PATH . '/pdfs/' . $tenantId . '/' . $pathOrKey;
        if (file_exists($localPath)) unlink($localPath);
    }
}

function getPrivateFileUrl($pathOrKey) {
    $tenantId = currentTenantId();

    // Check if file exists locally first
    $localPath = STORAGE_PATH . '/pdfs/' . $tenantId . '/' . $pathOrKey;
    if (file_exists($localPath)) {
        return ['type' => 'local', 'path' => $localPath];
    }

    // Treat as S3 key — return presigned URL
    if (\App\Services\S3Service::isConfigured()) {
        $s3 = new \App\Services\S3Service();
        $url = $s3->getPresignedUrl($pathOrKey);
        return ['type' => 's3', 'url' => $url];
    }

    return null;
}

function tenantFeature($feature) {
    $tenant = currentTenant();
    if (!$tenant) return false;
    $key = 'feature_' . $feature;
    return !empty($tenant[$key]);
}

/**
 * Get or create a long-lived anonymous visitor id (used for analytics / A-B stickiness).
 */
function getOrCreateVisitorId() {
    if (!empty($_COOKIE['kz_vid'])) {
        return $_COOKIE['kz_vid'];
    }
    $vid = bin2hex(random_bytes(16));
    setcookie('kz_vid', $vid, time() + (365 * 24 * 60 * 60), '/', '', true, false);
    $_COOKIE['kz_vid'] = $vid;
    return $vid;
}

/**
 * Rough device-type classification from the User-Agent (desktop|mobile|tablet).
 */
function detectDeviceType() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (preg_match('/iPad|Tablet|PlayBook|Silk|Android(?!.*Mobile)/i', $ua)) {
        return 'tablet';
    }
    if (preg_match('/Mobile|iPhone|iPod|Windows Phone|IEMobile|BlackBerry|Opera Mini/i', $ua)) {
        return 'mobile';
    }
    return 'desktop';
}

/**
 * Best-effort storefront page-view tracking for the analytics dashboard. Never throws.
 */
function recordPageView($tenantId, $pageType, $pageId = null) {
    if (!$tenantId) return;
    try {
        \App\Models\PageView::record([
            'tenant_id' => $tenantId,
            'page_type' => $pageType,
            'page_id' => $pageId,
            'page_url' => substr(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', 0, 500),
            'visitor_id' => getOrCreateVisitorId(),
            'user_id' => currentUserId(),
            'referrer' => isset($_SERVER['HTTP_REFERER']) ? substr($_SERVER['HTTP_REFERER'], 0, 500) : null,
            'utm_source' => isset($_GET['utm_source']) ? substr((string)$_GET['utm_source'], 0, 255) : null,
            'utm_medium' => isset($_GET['utm_medium']) ? substr((string)$_GET['utm_medium'], 0, 255) : null,
            'utm_campaign' => isset($_GET['utm_campaign']) ? substr((string)$_GET['utm_campaign'], 0, 255) : null,
            'device_type' => detectDeviceType(),
        ]);
    } catch (\Throwable $e) {
        if (APP_DEBUG) error_log('recordPageView failed: ' . $e->getMessage());
    }
}

/**
 * Assign (stickily, per visitor) an A/B-test variant for a page and count one view on
 * first assignment. Returns ['test_id','variant_row_id','page_id'] or null if no test
 * is running for this page. page_id is the page the chosen variant should render.
 */
function abAssignVariant($tenantId, $pageType, $pageId) {
    if (!$tenantId) return null;
    try {
        $variants = \App\Models\AbTest::findActiveForPage($tenantId, $pageType, $pageId);
        if (!$variants) return null;
        $testId = (int)$variants[0]['id'];
        $cookie = 'kz_ab_' . $testId;

        $chosen = null;
        if (!empty($_COOKIE[$cookie])) {
            $rowId = (int)$_COOKIE[$cookie];
            foreach ($variants as $v) {
                if ((int)$v['variant_id'] === $rowId) { $chosen = $v; break; }
            }
        }
        if (!$chosen) {
            $chosen = \App\Models\AbTest::selectVariant($variants);
            if (!$chosen) return null;
            setcookie($cookie, (string)$chosen['variant_id'], time() + (30 * 24 * 60 * 60), '/', '', true, false);
            $_COOKIE[$cookie] = (string)$chosen['variant_id'];
            \App\Models\AbTestVariant::incrementViews((int)$chosen['variant_id']);
        }
        // In findActiveForPage: variant_id = variant row id, variant_page_id = the page it renders
        return [
            'test_id' => $testId,
            'variant_row_id' => (int)$chosen['variant_id'],
            'page_id' => (int)$chosen['variant_page_id'],
        ];
    } catch (\Throwable $e) {
        if (APP_DEBUG) error_log('abAssignVariant failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Record an A/B conversion for the visitor's assigned variant whose rendered page matches
 * $shownPageId (the page the visitor actually converted on). Never throws.
 */
function abRecordConversion($tenantId, $pageType, $shownPageId) {
    if (!$tenantId) return;
    try {
        foreach ($_COOKIE as $name => $val) {
            if (strpos($name, 'kz_ab_') !== 0) continue;
            $testId = (int)substr($name, 6);
            if (!$testId) continue;
            $test = \App\Models\AbTest::find($testId, $tenantId);
            if (!$test || $test['status'] !== 'running' || $test['original_type'] !== $pageType) continue;
            $rowId = (int)$val;
            $variant = \App\Models\AbTestVariant::find($rowId);
            if (!$variant || (int)$variant['ab_test_id'] !== $testId) continue;
            if ((int)$variant['variant_id'] === (int)$shownPageId) {
                \App\Models\AbTestVariant::incrementConversions($rowId);
            }
        }
    } catch (\Throwable $e) {
        if (APP_DEBUG) error_log('abRecordConversion failed: ' . $e->getMessage());
    }
}
