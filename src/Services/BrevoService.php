<?php

namespace App\Services;

use App\Models\Setting;

class BrevoService {
    private string $apiKey;
    private string $apiBase = 'https://api.brevo.com/v3';

    /**
     * Constructor.
     * Priority: explicit $apiKey > per-tenant setting > platform default BREVO_API_KEY constant.
     */
    public function __construct(?string $apiKey = null, ?int $tenantId = null) {
        if ($apiKey) {
            $this->apiKey = $apiKey;
        } elseif ($tenantId) {
            $tenantKey = Setting::get('brevo_api_key', $tenantId);
            $this->apiKey = !empty($tenantKey) ? $tenantKey : (defined('BREVO_API_KEY') ? BREVO_API_KEY : '');
        } else {
            // Try current tenant from resolver
            $tenant = TenantResolver::current();
            if ($tenant) {
                $tenantKey = Setting::get('brevo_api_key', $tenant['id']);
                $this->apiKey = !empty($tenantKey) ? $tenantKey : (defined('BREVO_API_KEY') ? BREVO_API_KEY : '');
            } else {
                $this->apiKey = defined('BREVO_API_KEY') ? BREVO_API_KEY : '';
            }
        }
    }

    /**
     * Send a transactional email via Brevo SMTP API.
     *
     * @param array|string $to       Recipient email (string) or array with 'email' and optional 'name'
     * @param string       $subject  Email subject
     * @param string       $htmlContent HTML body
     * @param string|null  $fromEmail Sender email (falls back to tenant/platform setting)
     * @param string|null  $fromName  Sender name (falls back to tenant/platform setting)
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
        $fromEmail = $fromEmail ?? $this->resolveFromEmail();
        $fromName = $fromName ?? $this->resolveFromName();

        // Normalize $to to array format
        if (is_string($to)) {
            $toArray = [['email' => $to]];
        } elseif (isset($to['email'])) {
            $toArray = [$to];
        } else {
            $toArray = $to;
        }

        $payload = [
            'sender' => [
                'name' => $fromName,
                'email' => $fromEmail,
            ],
            'to' => $toArray,
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ];

        return $this->makeRequest('POST', '/smtp/email', $payload);
    }

    /**
     * Add (or update) a contact in Brevo.
     *
     * @param string   $email  Contact email
     * @param string   $name   Contact full name
     * @param int|null $listId Brevo list ID to add the contact to
     * @return bool True on success
     */
    public function addContact(string $email, string $name = '', ?int $listId = null): bool {
        $attributes = [];

        if (!empty($name)) {
            $nameParts = explode(' ', trim($name), 2);
            $attributes['FIRSTNAME'] = $nameParts[0];
            if (isset($nameParts[1])) {
                $attributes['LASTNAME'] = $nameParts[1];
            }
            $attributes['NAME'] = $name;
        }

        $attributes['SUBSCRIBED_AT'] = date('Y-m-d H:i:s');

        $contactData = [
            'email' => $email,
            'attributes' => $attributes,
            'updateEnabled' => true,
            'emailBlacklisted' => false,
            'smsBlacklisted' => false,
        ];

        if ($listId) {
            $contactData['listIds'] = [$listId];
        }

        try {
            $response = $this->makeRequest('POST', '/contacts', $contactData);
            error_log("BrevoService: Contact added/updated - {$email}");
            return true;
        } catch (\Exception $e) {
            // Handle duplicate: try PUT update instead
            if (str_contains($e->getMessage(), 'duplicate_parameter') || str_contains($e->getMessage(), 'Contact already exist')) {
                return $this->updateExistingContact($email, $attributes, $listId);
            }
            error_log("BrevoService: Failed to add contact - {$email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send lead magnet delivery email with download link.
     *
     * @param string $email       Recipient email
     * @param string $name        Recipient name
     * @param array  $leadMagnet  Lead magnet record (must have 'title', 'email_subject', 'email_body_html')
     * @param string $downloadUrl Tokenized download URL
     * @return array API response data
     * @throws \Exception on failure
     */
    public function sendLeadMagnetEmail(string $email, string $name, array $leadMagnet, string $downloadUrl): array {
        $subject = $leadMagnet['email_subject'] ?? 'Din gratis download: ' . ($leadMagnet['title'] ?? 'Lead Magnet');

        // Build HTML body from lead magnet template or use the stored HTML
        $htmlContent = $leadMagnet['email_body_html'] ?? '';

        if (empty($htmlContent)) {
            // Fallback template
            $firstName = explode(' ', trim($name), 2)[0] ?: 'der';
            $title = htmlspecialchars($leadMagnet['title'] ?? 'Download');
            $htmlContent = $this->buildLeadMagnetEmailHtml($firstName, $title, $downloadUrl);
        } else {
            // Replace placeholders in the stored template
            $htmlContent = str_replace(
                ['{{name}}', '{{first_name}}', '{{download_url}}', '{{title}}'],
                [
                    htmlspecialchars($name),
                    htmlspecialchars(explode(' ', trim($name), 2)[0]),
                    $downloadUrl,
                    htmlspecialchars($leadMagnet['title'] ?? ''),
                ],
                $htmlContent
            );
        }

        return $this->sendTransactionalEmail(
            ['email' => $email, 'name' => $name],
            $subject,
            $htmlContent
        );
    }

    /**
     * Check if this service instance has a valid API key configured.
     */
    public function isConfigured(): bool {
        return !empty($this->apiKey);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Update an existing Brevo contact via PUT.
     */
    private function updateExistingContact(string $email, array $attributes, ?int $listId = null): bool {
        try {
            $updateData = [
                'attributes' => $attributes,
                'emailBlacklisted' => false,
            ];

            if ($listId) {
                $updateData['listIds'] = [$listId];
            }

            $this->makeRequest('PUT', '/contacts/' . urlencode($email), $updateData);
            error_log("BrevoService: Contact updated - {$email}");
            return true;
        } catch (\Exception $e) {
            error_log("BrevoService: Failed to update contact - {$email}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Resolve sender email from tenant setting or platform default.
     */
    private function resolveFromEmail(): string {
        $tenant = TenantResolver::current();
        if ($tenant) {
            $tenantEmail = Setting::get('mail_from_address', $tenant['id']);
            if (!empty($tenantEmail)) {
                return $tenantEmail;
            }
        }
        return defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : 'info@kompaza.com';
    }

    /**
     * Resolve sender name from tenant setting or platform default.
     */
    private function resolveFromName(): string {
        $tenant = TenantResolver::current();
        if ($tenant) {
            $tenantName = Setting::get('mail_from_name', $tenant['id']);
            if (!empty($tenantName)) {
                return $tenantName;
            }
            // Fall back to tenant company name
            if (!empty($tenant['company_name'])) {
                return $tenant['company_name'];
            }
        }
        return defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'Kompaza';
    }

    /**
     * Make an authenticated request to the Brevo API.
     *
     * @param string     $method   HTTP method (GET, POST, PUT, DELETE)
     * @param string     $endpoint API endpoint path (e.g. /smtp/email)
     * @param array|null $data     Request body data
     * @return array Decoded JSON response
     * @throws \Exception on cURL or HTTP error
     */
    private function makeRequest(string $method, string $endpoint, ?array $data = null): array {
        $url = $this->apiBase . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'api-key: ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($data !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \Exception('Brevo cURL error: ' . $curlError);
        }

        $responseData = json_decode($response, true) ?? [];

        if ($httpCode >= 400) {
            $errorMessage = $responseData['message'] ?? 'Unknown error';
            $errorCode = $responseData['code'] ?? '';
            throw new \Exception("Brevo API error (HTTP {$httpCode}): {$errorMessage}" . ($errorCode ? " [{$errorCode}]" : ''));
        }

        return $responseData;
    }

    /**
     * Build a default HTML email template for lead magnet delivery.
     */
    private function buildLeadMagnetEmailHtml(string $firstName, string $title, string $downloadUrl): string {
        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #1e40af;">Hej {$firstName}!</h2>
    <p>Tak fordi du tilmeldte dig. Her er dit download-link til <strong>{$title}</strong>:</p>
    <p style="text-align: center; margin: 30px 0;">
        <a href="{$downloadUrl}" style="background-color: #2563eb; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">
            Download nu
        </a>
    </p>
    <p style="font-size: 13px; color: #666;">Linket er gyldigt i 72 timer. Kontakt os hvis du oplever problemer.</p>
</body>
</html>
HTML;
    }
}
