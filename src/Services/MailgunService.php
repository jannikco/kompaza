<?php

namespace App\Services;

class MailgunService {
    private string $apiKey;
    private string $domain;

    public function __construct(string $apiKey, string $domain) {
        $this->apiKey = $apiKey;
        $this->domain = $domain;
    }

    /**
     * Send a transactional email via Mailgun API.
     *
     * @param array|string $to         Recipient email (string) or array with 'email' and optional 'name'
     * @param string       $subject    Email subject
     * @param string       $htmlContent HTML body
     * @param string|null  $fromEmail  Sender email
     * @param string|null  $fromName   Sender name
     * @return array API response data
     * @throws \Exception on cURL or API error
     */
    public function sendTransactionalEmail(
        $to,
        string $subject,
        string $htmlContent,
        ?string $fromEmail = null,
        ?string $fromName = null
    ): array {
        $fromEmail = $fromEmail ?? EmailHelper::resolveFromEmail();
        $fromName = $fromName ?? EmailHelper::resolveFromName();

        // Build "From" header
        $from = $fromName ? "{$fromName} <{$fromEmail}>" : $fromEmail;

        // Normalize $to
        if (is_string($to)) {
            $toStr = $to;
        } elseif (isset($to['email'])) {
            $toStr = !empty($to['name']) ? "{$to['name']} <{$to['email']}>" : $to['email'];
        } else {
            // Array of recipients
            $parts = [];
            foreach ($to as $recipient) {
                $parts[] = !empty($recipient['name'])
                    ? "{$recipient['name']} <{$recipient['email']}>"
                    : $recipient['email'];
            }
            $toStr = implode(', ', $parts);
        }

        $postFields = [
            'from'    => $from,
            'to'      => $toStr,
            'subject' => $subject,
            'html'    => $htmlContent,
        ];

        $url = "https://api.mailgun.net/v3/{$this->domain}/messages";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERPWD, 'api:' . $this->apiKey);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \Exception('Mailgun cURL error: ' . $curlError);
        }

        $responseData = json_decode($response, true) ?? [];

        if ($httpCode >= 400) {
            $errorMessage = $responseData['message'] ?? 'Unknown error';
            throw new \Exception("Mailgun API error (HTTP {$httpCode}): {$errorMessage}");
        }

        return $responseData;
    }

    /**
     * Send lead magnet delivery email with download link.
     */
    public function sendLeadMagnetEmail(string $email, string $name, array $leadMagnet, string $downloadUrl): array {
        $built = EmailHelper::buildLeadMagnetEmail($email, $name, $leadMagnet, $downloadUrl);

        return $this->sendTransactionalEmail(
            ['email' => $email, 'name' => $name],
            $built['subject'],
            $built['htmlContent']
        );
    }

    /**
     * Check if this service instance is properly configured.
     */
    public function isConfigured(): bool {
        return !empty($this->apiKey) && !empty($this->domain);
    }
}
