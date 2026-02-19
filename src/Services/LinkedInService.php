<?php

namespace App\Services;

class LinkedInService {
    private string $liAtCookie;
    private string $csrfToken;
    private string $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36';

    /**
     * Constructor.
     *
     * @param string $liAtCookie LinkedIn li_at session cookie value
     * @param string $csrfToken  LinkedIn JSESSIONID / csrf token value
     */
    public function __construct(string $liAtCookie, string $csrfToken) {
        $this->liAtCookie = $liAtCookie;
        // Strip surrounding quotes from JSESSIONID if present
        $this->csrfToken = trim($csrfToken, '"');
    }

    /**
     * Validate the LinkedIn session cookie by fetching the current user's profile.
     *
     * @return array|false Profile data array on success, false on failure
     */
    public function validateCookie() {
        $url = 'https://www.linkedin.com/voyager/api/identity/profiles/me';

        $response = $this->makeRequest($url);

        if ($response === false) {
            return false;
        }

        return $response;
    }

    /**
     * Fetch a LinkedIn profile's details.
     *
     * @param string $profileUrl Full LinkedIn profile URL or public identifier
     * @return array|false Profile data or false on failure
     */
    public function fetchProfile(string $profileUrl) {
        $this->randomDelay();

        $publicId = $this->extractPublicIdentifier($profileUrl);
        if (!$publicId) {
            error_log("LinkedInService: Could not extract public identifier from URL: {$profileUrl}");
            return false;
        }

        $url = 'https://www.linkedin.com/voyager/api/identity/profiles/' . urlencode($publicId);

        return $this->makeRequest($url);
    }

    /**
     * Search for people on LinkedIn.
     *
     * @param string $searchUrl LinkedIn search URL or keywords
     * @param int    $page      Zero-based page number (each page = 10 results)
     * @return array|false Array of search results or false on failure
     */
    public function searchPeople(string $searchUrl, int $page = 0) {
        $this->randomDelay();

        $start = $page * 10;
        $count = 10;

        // If it's a full URL, extract keywords; otherwise treat as keywords
        if (str_contains($searchUrl, 'linkedin.com')) {
            $parsed = parse_url($searchUrl);
            parse_str($parsed['query'] ?? '', $queryParams);
            $keywords = $queryParams['keywords'] ?? '';
        } else {
            $keywords = $searchUrl;
        }

        $params = http_build_query([
            'keywords' => $keywords,
            'origin' => 'GLOBAL_SEARCH_HEADER',
            'start' => $start,
            'count' => $count,
        ]);

        $url = 'https://www.linkedin.com/voyager/api/search/dash/clusters?' . $params;

        return $this->makeRequest($url);
    }

    /**
     * Send a connection request (invitation) to a LinkedIn profile.
     *
     * @param string $profileUrn LinkedIn profile URN (e.g. "urn:li:fsd_profile:ACoAAB...")
     * @param string $message    Optional personal message (max 300 chars)
     * @return array|false API response or false on failure
     */
    public function sendConnectionRequest(string $profileUrn, string $message = '') {
        $this->randomDelay();

        $url = 'https://www.linkedin.com/voyager/api/voyagerRelationshipsDashMemberRelationships?action=verifyQuotaAndCreate';

        $data = [
            'invitee' => [
                'inviteeUnion' => [
                    'memberProfile' => $profileUrn,
                ],
            ],
        ];

        if (!empty($message)) {
            $data['customMessage'] = mb_substr($message, 0, 300);
        }

        return $this->makeRequest($url, 'POST', $data);
    }

    /**
     * Send a message to a LinkedIn profile.
     *
     * @param string $profileUrn LinkedIn profile URN
     * @param string $message    Message body text
     * @return array|false API response or false on failure
     */
    public function sendMessage(string $profileUrn, string $message) {
        $this->randomDelay();

        $url = 'https://www.linkedin.com/voyager/api/voyagerMessagingDashMessengerMessages?action=createMessage';

        $data = [
            'message' => [
                'body' => [
                    'text' => $message,
                ],
            ],
            'mailboxUrn' => $profileUrn,
            'hostRecipientUrns' => [$profileUrn],
        ];

        return $this->makeRequest($url, 'POST', $data);
    }

    /**
     * Simulate viewing a LinkedIn profile (triggers a profile view notification).
     *
     * @param string $profileUrl Full LinkedIn profile URL or public identifier
     * @return array|false API response or false on failure
     */
    public function viewProfile(string $profileUrl) {
        $this->randomDelay();

        $publicId = $this->extractPublicIdentifier($profileUrl);
        if (!$publicId) {
            error_log("LinkedInService: Could not extract public identifier from URL: {$profileUrl}");
            return false;
        }

        // Fetching the profile with specific fields triggers a profile view
        $url = 'https://www.linkedin.com/voyager/api/identity/profiles/' . urlencode($publicId) . '/profileView';

        return $this->makeRequest($url, 'POST');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Make an authenticated request to the LinkedIn Voyager API.
     *
     * @param string     $url    Full API URL
     * @param string     $method HTTP method (GET or POST)
     * @param array|null $data   POST data (will be JSON-encoded)
     * @return array|false Parsed JSON response or false on failure
     */
    private function makeRequest(string $url, string $method = 'GET', ?array $data = null) {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $headers = [
            'User-Agent: ' . $this->userAgent,
            'Accept: application/vnd.linkedin.normalized+json+2.1',
            'Accept-Language: en-US,en;q=0.9,da;q=0.8',
            'x-li-lang: en_US',
            'x-li-track: {"clientVersion":"1.13.8857","mpVersion":"1.13.8857","osName":"web","timezoneOffset":1,"timezone":"Europe/Copenhagen","deviceFormFactor":"DESKTOP","mpName":"voyager-web","displayDensity":1,"displayWidth":1920,"displayHeight":1080}',
            'x-restli-protocol-version: 2.0.0',
            'csrf-token: ' . $this->csrfToken,
            'Cookie: li_at=' . $this->liAtCookie . '; JSESSIONID="' . $this->csrfToken . '"',
        ];

        if ($data !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Content-Length: ' . strlen($jsonData);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log("LinkedInService cURL error: {$curlError}");
            return false;
        }

        // LinkedIn returns 401/403 when the session is invalid
        if ($httpCode === 401 || $httpCode === 403) {
            error_log("LinkedInService: Authentication failed (HTTP {$httpCode}). Cookie may be expired.");
            return false;
        }

        if ($httpCode >= 400) {
            error_log("LinkedInService: Request failed (HTTP {$httpCode}): " . substr($response, 0, 500));
            return false;
        }

        // Some responses (204 No Content) may be empty
        if (empty($response)) {
            return [];
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("LinkedInService: Failed to decode JSON response: " . json_last_error_msg());
            return false;
        }

        return $decoded;
    }

    /**
     * Extract the public identifier (vanity name) from a LinkedIn profile URL.
     * e.g. "https://www.linkedin.com/in/johndoe/" => "johndoe"
     *
     * @param string $profileUrl Full URL or just the public ID
     * @return string|null Public identifier or null if extraction fails
     */
    private function extractPublicIdentifier(string $profileUrl): ?string {
        // Already a plain identifier (no slashes or protocol)
        if (!str_contains($profileUrl, '/') && !str_contains($profileUrl, ':')) {
            return $profileUrl;
        }

        // Extract from URL: /in/{publicId}
        if (preg_match('#/in/([^/?#]+)#', $profileUrl, $matches)) {
            return rtrim($matches[1], '/');
        }

        return null;
    }

    /**
     * Sleep for a random duration between 2 and 8 seconds to mimic human behavior.
     */
    private function randomDelay(): void {
        $microseconds = random_int(2000000, 8000000);
        usleep($microseconds);
    }
}
