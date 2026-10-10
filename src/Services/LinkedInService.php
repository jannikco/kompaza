<?php

namespace App\Services;

use App\Models\LinkedInAccount;
use App\Services\LinkedIn\RecentPostsParser;

class LinkedInService {
    private string $liAtCookie;
    private string $csrfToken;
    private ?string $senderUrn;
    private ?string $publicIdentifier = null;
    private bool $delayEnabled = true;
    private int $lastHttpStatus = 0;
    private ?string $lastError = null;
    private string $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36';
    private string $acceptLanguage = 'en-US,en;q=0.9,da;q=0.8';
    private string $liTrackJson = '{"clientVersion":"1.13.8857","mpVersion":"1.13.8857","osName":"web","timezoneOffset":1,"timezone":"Europe/Copenhagen","deviceFormFactor":"DESKTOP","mpName":"voyager-web","displayDensity":1,"displayWidth":1920,"displayHeight":1080}';

    /**
     * Constructor.
     *
     * @param string      $liAtCookie LinkedIn li_at session cookie value
     * @param string      $csrfToken  LinkedIn JSESSIONID / csrf token value
     * @param string|null $senderUrn  The CONNECTED account's own profile URN
     *                                (linkedin_accounts.profile_urn). Required to send
     *                                a message — see sendMessage().
     */
    public function __construct(string $liAtCookie, string $csrfToken, ?string $senderUrn = null) {
        $this->liAtCookie = $liAtCookie;
        // Strip surrounding quotes from JSESSIONID if present
        $this->csrfToken = trim($csrfToken, '"');
        $this->senderUrn = $senderUrn ?: null;
    }

    /** Set the connected account's own profile URN after construction. */
    public function setSenderUrn(?string $urn): void {
        $this->senderUrn = $urn ?: null;
    }

    /**
     * Build a client from a stored (encrypted) account, including the browser
     * fingerprint the extension mirrored. This is the constructor cron and
     * ActionExecutor should use — the hardcoded UA/clientVersion above is only
     * a fallback for the cookie-paste "Test connection" path.
     */
    public static function forAccount(int $accountId): self
    {
        $row = LinkedInAccount::find($accountId);
        if (!$row) {
            throw new \RuntimeException("LinkedIn account {$accountId} not found.");
        }
        $session = LinkedInAccount::session($accountId);
        $svc = new self($session['li_at'], $session['jsessionid'], $row['profile_urn'] ?? null);
        $svc->publicIdentifier = $row['public_identifier'] ?? null;
        $svc->applyFingerprint($row);
        return $svc;
    }

    public function withoutDelay(): self
    {
        $this->delayEnabled = false;
        return $this;
    }

    public function lastHttpStatus(): int
    {
        return $this->lastHttpStatus;
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    /** @param array<string,mixed> $row */
    public function applyFingerprint(array $row): void
    {
        if (!empty($row['user_agent'])) {
            $this->userAgent = (string) $row['user_agent'];
        }
        if (!empty($row['accept_language'])) {
            $this->acceptLanguage = (string) $row['accept_language'];
        }
        $track = $row['li_track_json'] ?? null;
        if (is_array($track)) {
            $this->liTrackJson = json_encode($track, JSON_UNESCAPED_SLASHES) ?: $this->liTrackJson;
        } elseif (is_string($track) && $track !== '') {
            $this->liTrackJson = $track;
        }
    }

    /**
     * Extract the connected member's own identifiers from a validateCookie() response.
     *
     * Nothing populated linkedin_accounts.profile_urn before, which is why
     * sendMessage() had no sender URN to address the mailbox with.
     *
     * @return array{profile_urn: ?string, member_urn: ?string, public_identifier: ?string}
     */
    public static function extractSelfIdentity(array $profile): array {
        $mini = $profile['miniProfile'] ?? $profile;

        $profileUrn = $profile['entityUrn']
            ?? $profile['dashEntityUrn']
            ?? $mini['entityUrn']
            ?? null;
        // /voyager/api/me returns "urn:li:fs_miniProfile:ACoAA..."; the messaging and
        // invitation endpoints want the fsd_profile form of the same id.
        if (is_string($profileUrn) && preg_match('/(ACoA[A-Za-z0-9_-]+)/', $profileUrn, $m)) {
            $profileUrn = 'urn:li:fsd_profile:' . $m[1];
        }

        $memberUrn = $profile['objectUrn'] ?? $mini['objectUrn'] ?? null;
        if (!is_string($memberUrn) || !str_contains((string)$memberUrn, ':member:')) {
            $memberUrn = null;
        }

        $publicId = $mini['publicIdentifier'] ?? $profile['publicIdentifier'] ?? null;

        return [
            'profile_urn'       => is_string($profileUrn) ? $profileUrn : null,
            'member_urn'        => $memberUrn,
            'public_identifier' => is_string($publicId) ? $publicId : null,
        ];
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
        // `mailboxUrn` is the SENDER's mailbox — the connected account — while
        // `hostRecipientUrns` names who receives it. This previously passed the
        // recipient's URN as both, so the request addressed the recipient's own
        // mailbox. Nothing populated linkedin_accounts.profile_urn either, so there
        // was no sender URN available to pass.
        if ($this->senderUrn === null) {
            error_log(
                'LinkedInService::sendMessage refused: no sender URN configured. '
                . 'Populate linkedin_accounts.profile_urn (see extractSelfIdentity) '
                . 'and pass it to the constructor.'
            );
            return false;
        }

        $this->randomDelay();

        $url = 'https://www.linkedin.com/voyager/api/voyagerMessagingDashMessengerMessages?action=createMessage';

        $data = [
            'message' => [
                'body' => [
                    'text' => $message,
                ],
            ],
            'mailboxUrn'        => $this->senderUrn,
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

    /**
     * Fetch comments on a LinkedIn post.
     *
     * @param string $postUrn Post URN (e.g. "urn:li:activity:123")
     * @param int    $start   Pagination start offset
     * @param int    $count   Number of comments to fetch
     * @return array|false Array of comment data or false on failure
     */
    public function getPostComments(string $postUrn, int $start = 0, int $count = 20) {
        $this->randomDelay();

        $params = http_build_query([
            'commentsUrn' => $postUrn,
            'count' => $count,
            'start' => $start,
            'sortOrder' => 'REVERSE_CHRONOLOGICAL',
        ]);

        $url = 'https://www.linkedin.com/voyager/api/feed/dash/comments?' . $params;

        return $this->makeRequest($url);
    }

    /**
     * Post a comment (or reply to a comment) on a LinkedIn post.
     *
     * @param string      $postUrn          Post URN (thread)
     * @param string      $text             Comment text
     * @param string|null $parentCommentUrn Parent comment URN for replies
     * @param list<array{start:int,length:int,urn:string}> $mentions
     * @return array|false API response or false on failure
     */
    public function postComment(string $postUrn, string $text, ?string $parentCommentUrn = null, array $mentions = []) {
        $this->randomDelay();

        $url = 'https://www.linkedin.com/voyager/api/feed/dash/comments';

        $commentary = $text;
        if ($mentions !== []) {
            $attributes = [];
            foreach ($mentions as $mention) {
                $attributes[] = [
                    'start' => (int) $mention['start'],
                    'length' => (int) $mention['length'],
                    'detailDataUnion' => [
                        'profileMention' => $mention['urn'],
                    ],
                ];
            }
            $commentary = [
                'text' => $text,
                'attributesV2' => $attributes,
            ];
        }

        $data = [
            'commentary' => $commentary,
            'threadUrn' => $postUrn,
        ];

        if ($parentCommentUrn) {
            $data['parentComment'] = $parentCommentUrn;
        }

        return $this->makeRequest($url, 'POST', $data);
    }

    /**
     * React to a post or a comment thread.
     *
     * @param string $threadUrn    activity / ugcPost / comment URN
     * @param string $reactionType LIKE, EMPATHY, INTEREST, PRAISE, APPRECIATION, ENTERTAINMENT
     */
    public function reactToPost(string $threadUrn, string $reactionType = 'LIKE') {
        $this->randomDelay();

        $allowed = ['LIKE', 'EMPATHY', 'INTEREST', 'PRAISE', 'APPRECIATION', 'ENTERTAINMENT'];
        $reactionType = strtoupper($reactionType);
        if (!in_array($reactionType, $allowed, true)) {
            $reactionType = 'LIKE';
        }

        $url = 'https://www.linkedin.com/voyager/api/voyagerSocialDashReactions?threadUrn='
            . rawurlencode($threadUrn);

        return $this->makeRequest($url, 'POST', [
            'reactionType' => $reactionType,
        ]);
    }

    /**
     * The connected member's recent posts plus engagement counts.
     *
     * @return list<array>|false
     */
    public function listRecentPosts(int $count = 10, int $start = 0) {
        $publicId = $this->publicIdentifier;
        if (!$publicId) {
            $me = $this->validateCookie();
            if ($me === false) {
                return false;
            }
            $identity = self::extractSelfIdentity($me);
            $publicId = $identity['public_identifier'];
            $this->publicIdentifier = $publicId;
            if ($this->senderUrn === null) {
                $this->senderUrn = $identity['profile_urn'];
            }
        }
        if (!$publicId) {
            $this->lastError = 'Could not resolve the connected profile identifier.';
            return false;
        }

        $params = http_build_query([
            'moduleKey' => 'creator_profile_all_content_view',
            'count'     => $count,
            'start'     => $start,
            'q'         => 'memberShareFeed',
            'profileId' => $publicId,
        ]);
        $response = $this->makeRequest(
            'https://www.linkedin.com/voyager/api/identity/profileUpdatesV2?' . $params
        );
        if ($response === false) {
            $fallbackUrn = $this->senderUrn ?: ('urn:li:fsd_profile:' . $publicId);
            $response = $this->makeRequest(
                'https://www.linkedin.com/voyager/api/identity/dash/profiles/'
                . rawurlencode($fallbackUrn)
                . '/profileUpdates?count=' . $count . '&start=' . $start
            );
        }
        if ($response === false) {
            return false;
        }

        return RecentPostsParser::parse($response);
    }

    /**
     * Extract a LinkedIn post URN from various URL formats.
     *
     * Supported formats:
     * - linkedin.com/feed/update/urn:li:activity:123/
     * - linkedin.com/feed/update/urn:li:ugcPost:123/
     * - linkedin.com/posts/username_slug-123-abcd/
     *
     * @param string $postUrl LinkedIn post URL
     * @return string|null Extracted URN or null on failure
     */
    public static function extractPostUrn(string $postUrl): ?string {
        // Direct activity URN in URL
        if (preg_match('#/feed/update/(urn:li:activity:\d+)#', $postUrl, $matches)) {
            return $matches[1];
        }

        // UGC post URN in URL
        if (preg_match('#/feed/update/(urn:li:ugcPost:\d+)#', $postUrl, $matches)) {
            return $matches[1];
        }

        // Posts URL format: /posts/username_slug-ACTIVITYID-abcd
        // The activity ID is typically a 19-digit number
        if (preg_match('#/posts/[^/]+-(\d{19,})-#', $postUrl, $matches)) {
            return 'urn:li:activity:' . $matches[1];
        }

        // Fallback: try to find any activity ID in the URL
        if (preg_match('#activity[:\-](\d{19,})#', $postUrl, $matches)) {
            return 'urn:li:activity:' . $matches[1];
        }

        return null;
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
        $this->lastHttpStatus = 0;
        $this->lastError = null;

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $headers = [
            'User-Agent: ' . $this->userAgent,
            'Accept: application/vnd.linkedin.normalized+json+2.1',
            'Accept-Language: ' . $this->acceptLanguage,
            'x-li-lang: en_US',
            'x-li-track: ' . $this->liTrackJson,
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
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $this->lastHttpStatus = $httpCode;

        if ($curlError) {
            $this->lastError = $curlError;
            error_log("LinkedInService cURL error: {$curlError}");
            return false;
        }

        // LinkedIn returns 401/403 when the session is invalid
        if ($httpCode === 401 || $httpCode === 403) {
            $this->lastError = "Authentication failed (HTTP {$httpCode}). Cookie may be expired.";
            error_log("LinkedInService: {$this->lastError}");
            return false;
        }

        if ($httpCode >= 400) {
            $this->lastError = "Request failed (HTTP {$httpCode})";
            error_log("LinkedInService: Request failed (HTTP {$httpCode}): " . substr((string) $response, 0, 500));
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
        if (!$this->delayEnabled) {
            return;
        }
        $microseconds = random_int(2000000, 8000000);
        usleep($microseconds);
    }
}
