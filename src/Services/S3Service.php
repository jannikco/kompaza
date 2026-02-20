<?php

namespace App\Services;

class S3Service {
    private string $endpoint;
    private string $region;
    private string $accessKeyId;
    private string $secretAccessKey;
    private string $bucket;
    private string $publicDomain;

    public function __construct() {
        $this->endpoint = S3_ENDPOINT;
        $this->region = S3_REGION;
        $this->accessKeyId = S3_ACCESS_KEY_ID;
        $this->secretAccessKey = S3_SECRET_ACCESS_KEY;
        $this->bucket = S3_BUCKET_NAME;
        $this->publicDomain = S3_PUBLIC_DOMAIN;
    }

    public static function isConfigured(): bool {
        return !empty(S3_ACCESS_KEY_ID) && !empty(S3_SECRET_ACCESS_KEY) && !empty(S3_BUCKET_NAME);
    }

    public function getPublicUrl(string $key): string {
        return 'https://' . $this->publicDomain . '/' . ltrim($key, '/');
    }

    public function putObject(string $key, string $filePath, string $contentType, string $acl = ''): bool {
        $body = file_get_contents($filePath);
        $bodyHash = hash('sha256', $body);
        $date = gmdate('Ymd\THis\Z');
        $dateShort = gmdate('Ymd');

        $host = $this->bucket . '.' . parse_url($this->endpoint, PHP_URL_HOST);
        $uri = '/' . ltrim($key, '/');

        $headers = [
            'host' => $host,
            'x-amz-content-sha256' => $bodyHash,
            'x-amz-date' => $date,
            'content-type' => $contentType,
            'content-length' => strlen($body),
        ];
        if ($acl !== '') {
            $headers['x-amz-acl'] = $acl;
        }

        $authorization = $this->buildAuthorizationHeader('PUT', $uri, '', $headers, $bodyHash, $date, $dateShort);
        $headers['authorization'] = $authorization;

        $curlHeaders = [];
        foreach ($headers as $k => $v) {
            $curlHeaders[] = "$k: $v";
        }

        $url = "https://$host$uri";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_HTTPHEADER => $curlHeaders,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 300,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }

    public function putObjectStream(string $key, string $filePath, string $contentType, string $acl = ''): bool {
        $fileSize = filesize($filePath);
        $bodyHash = 'UNSIGNED-PAYLOAD';
        $date = gmdate('Ymd\THis\Z');
        $dateShort = gmdate('Ymd');

        $host = $this->bucket . '.' . parse_url($this->endpoint, PHP_URL_HOST);
        $uri = '/' . ltrim($key, '/');

        $headers = [
            'host' => $host,
            'x-amz-content-sha256' => $bodyHash,
            'x-amz-date' => $date,
            'content-type' => $contentType,
            'content-length' => $fileSize,
        ];
        if ($acl !== '') {
            $headers['x-amz-acl'] = $acl;
        }

        $authorization = $this->buildAuthorizationHeader('PUT', $uri, '', $headers, $bodyHash, $date, $dateShort);
        $headers['authorization'] = $authorization;

        $curlHeaders = [];
        foreach ($headers as $k => $v) {
            $curlHeaders[] = "$k: $v";
        }

        $fh = fopen($filePath, 'rb');
        $url = "https://$host$uri";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_PUT => true,
            CURLOPT_HTTPHEADER => $curlHeaders,
            CURLOPT_INFILE => $fh,
            CURLOPT_INFILESIZE => $fileSize,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 600,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fh);

        return $httpCode >= 200 && $httpCode < 300;
    }

    public function getPresignedUrl(string $key, int $expiresInSeconds = 14400): string {
        $date = gmdate('Ymd\THis\Z');
        $dateShort = gmdate('Ymd');
        $host = $this->bucket . '.' . parse_url($this->endpoint, PHP_URL_HOST);
        $uri = '/' . ltrim($key, '/');
        $credential = $this->accessKeyId . '/' . $dateShort . '/' . $this->region . '/s3/aws4_request';

        $queryParams = [
            'X-Amz-Algorithm' => 'AWS4-HMAC-SHA256',
            'X-Amz-Credential' => $credential,
            'X-Amz-Date' => $date,
            'X-Amz-Expires' => $expiresInSeconds,
            'X-Amz-SignedHeaders' => 'host',
        ];
        ksort($queryParams);
        $queryString = http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);

        $canonicalRequest = "GET\n$uri\n$queryString\nhost:$host\n\nhost\nUNSIGNED-PAYLOAD";
        $stringToSign = "AWS4-HMAC-SHA256\n$date\n$dateShort/{$this->region}/s3/aws4_request\n" . hash('sha256', $canonicalRequest);
        $signingKey = $this->getSigningKey($dateShort);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        return "https://$host$uri?$queryString&X-Amz-Signature=$signature";
    }

    public function deleteObject(string $key): bool {
        $bodyHash = hash('sha256', '');
        $date = gmdate('Ymd\THis\Z');
        $dateShort = gmdate('Ymd');

        $host = $this->bucket . '.' . parse_url($this->endpoint, PHP_URL_HOST);
        $uri = '/' . ltrim($key, '/');

        $headers = [
            'host' => $host,
            'x-amz-content-sha256' => $bodyHash,
            'x-amz-date' => $date,
        ];

        $authorization = $this->buildAuthorizationHeader('DELETE', $uri, '', $headers, $bodyHash, $date, $dateShort);
        $headers['authorization'] = $authorization;

        $curlHeaders = [];
        foreach ($headers as $k => $v) {
            $curlHeaders[] = "$k: $v";
        }

        $url = "https://$host$uri";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => $curlHeaders,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }

    public function headObject(string $key): ?array {
        $bodyHash = hash('sha256', '');
        $date = gmdate('Ymd\THis\Z');
        $dateShort = gmdate('Ymd');

        $host = $this->bucket . '.' . parse_url($this->endpoint, PHP_URL_HOST);
        $uri = '/' . ltrim($key, '/');

        $headers = [
            'host' => $host,
            'x-amz-content-sha256' => $bodyHash,
            'x-amz-date' => $date,
        ];

        $authorization = $this->buildAuthorizationHeader('HEAD', $uri, '', $headers, $bodyHash, $date, $dateShort);
        $headers['authorization'] = $authorization;

        $curlHeaders = [];
        foreach ($headers as $k => $v) {
            $curlHeaders[] = "$k: $v";
        }

        $url = "https://$host$uri";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'HEAD',
            CURLOPT_HTTPHEADER => $curlHeaders,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) return null;

        return [
            'content_length' => curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD),
            'content_type' => curl_getinfo($ch, CURLINFO_CONTENT_TYPE),
        ];
    }

    private function buildAuthorizationHeader(string $method, string $uri, string $queryString, array $headers, string $bodyHash, string $date, string $dateShort): string {
        ksort($headers);
        $canonicalHeaders = '';
        $signedHeadersList = [];
        foreach ($headers as $k => $v) {
            $lk = strtolower($k);
            $canonicalHeaders .= "$lk:" . trim($v) . "\n";
            $signedHeadersList[] = $lk;
        }
        $signedHeaders = implode(';', $signedHeadersList);

        $canonicalRequest = "$method\n$uri\n$queryString\n$canonicalHeaders\n$signedHeaders\n$bodyHash";
        $scope = "$dateShort/{$this->region}/s3/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n$date\n$scope\n" . hash('sha256', $canonicalRequest);

        $signingKey = $this->getSigningKey($dateShort);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        return "AWS4-HMAC-SHA256 Credential={$this->accessKeyId}/$scope, SignedHeaders=$signedHeaders, Signature=$signature";
    }

    private function getSigningKey(string $dateShort): string {
        $kDate = hash_hmac('sha256', $dateShort, "AWS4{$this->secretAccessKey}", true);
        $kRegion = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', 's3', $kRegion, true);
        return hash_hmac('sha256', 'aws4_request', $kService, true);
    }

    public function getKeyForCourseVideo(int $tenantId, int $courseId, int $lessonId, string $filename): string {
        return "tenants/$tenantId/courses/$courseId/lessons/$lessonId/video/$filename";
    }

    public function getKeyForCourseThumbnail(int $tenantId, int $courseId, int $lessonId, string $filename): string {
        return "tenants/$tenantId/courses/$courseId/lessons/$lessonId/thumbnails/$filename";
    }
}
