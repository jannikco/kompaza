<?php

namespace App\Services;

class Smtp2goService {
    private string $apiKey;
    private string $apiBase = 'https://api.smtp2go.com/v3';

    /**
     * @param string|null $apiKey Explicit key, or fall back to SMTP2GO_API_KEY constant
     */
    public function __construct(?string $apiKey = null) {
        if ($apiKey !== null && $apiKey !== '') {
            $this->apiKey = $apiKey;
        } else {
            $this->apiKey = defined('SMTP2GO_API_KEY') ? SMTP2GO_API_KEY : '';
        }
    }

    /**
     * Send a transactional email via SMTP2GO REST API.
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

        $sender = $fromName !== '' ? "{$fromName} <{$fromEmail}>" : $fromEmail;
        $toList = $this->normalizeRecipients($to);

        $payload = [
            'sender'    => $sender,
            'to'        => $toList,
            'subject'   => $subject,
            'html_body' => $htmlContent,
        ];

        return $this->makeRequest('POST', '/email/send', $payload);
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
     * Check if this service instance has a valid API key configured.
     */
    public function isConfigured(): bool {
        return $this->apiKey !== '';
    }

    /**
     * Normalize recipients to SMTP2GO's array-of-strings format.
     *
     * @param array|string $to
     * @return string[]
     */
    private function normalizeRecipients($to): array {
        if (is_string($to)) {
            return [$to];
        }

        if (isset($to['email'])) {
            $name = $to['name'] ?? '';
            return [$name !== '' ? "{$name} <{$to['email']}>" : $to['email']];
        }

        $parts = [];
        foreach ($to as $recipient) {
            if (is_string($recipient)) {
                $parts[] = $recipient;
                continue;
            }
            $email = $recipient['email'] ?? '';
            if ($email === '') {
                continue;
            }
            $name = $recipient['name'] ?? '';
            $parts[] = $name !== '' ? "{$name} <{$email}>" : $email;
        }

        return $parts;
    }

    /**
     * @return array Decoded JSON response
     * @throws \Exception on cURL or HTTP error
     */
    private function makeRequest(string $method, string $endpoint, ?array $data = null): array {
        if ($this->apiKey === '') {
            throw new \Exception('SMTP2GO API key is not configured');
        }

        $url = $this->apiBase . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Smtp2go-Api-Key: ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($data !== null && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \Exception('SMTP2GO cURL error: ' . $curlError);
        }

        $responseData = json_decode((string) $response, true) ?? [];

        if ($httpCode >= 400) {
            $errorMessage = $responseData['data']['error']
                ?? $responseData['error']
                ?? $responseData['message']
                ?? 'Unknown error';
            $errorCode = $responseData['data']['error_code'] ?? '';
            throw new \Exception(
                "SMTP2GO API error (HTTP {$httpCode}): {$errorMessage}"
                . ($errorCode !== '' ? " [{$errorCode}]" : '')
            );
        }

        // SMTP2GO can return 200 with failed counts in data
        $failed = (int) ($responseData['data']['failed'] ?? 0);
        if ($failed > 0) {
            $failures = $responseData['data']['failures'] ?? [];
            $detail = is_array($failures) ? implode('; ', array_map('strval', $failures)) : (string) $failures;
            throw new \Exception('SMTP2GO send failed: ' . ($detail !== '' ? $detail : 'unknown failure'));
        }

        return $responseData;
    }
}
