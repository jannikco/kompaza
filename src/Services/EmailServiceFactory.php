<?php

namespace App\Services;

class EmailServiceFactory {

    /**
     * Create the appropriate email service based on tenant's email_service setting.
     *
     * @return BrevoService|MailgunService|SmtpService
     */
    public static function create(?array $tenant = null): BrevoService|MailgunService|SmtpService {
        $tenant = $tenant ?? TenantResolver::current();
        $provider = $tenant['email_service'] ?? 'kompaza';

        switch ($provider) {
            case 'brevo':
                $apiKey = $tenant['brevo_api_key'] ?? '';
                return new BrevoService($apiKey ?: null);

            case 'mailgun':
                return new MailgunService(
                    $tenant['mailgun_api_key'] ?? '',
                    $tenant['mailgun_domain'] ?? ''
                );

            case 'smtp':
                return new SmtpService(
                    $tenant['smtp_host'] ?? '',
                    (int)($tenant['smtp_port'] ?? 587),
                    $tenant['smtp_username'] ?? '',
                    $tenant['smtp_password'] ?? '',
                    $tenant['smtp_encryption'] ?? 'tls'
                );

            case 'kompaza':
            default:
                // Use platform default Brevo key
                return new BrevoService(defined('BREVO_API_KEY') ? BREVO_API_KEY : null);
        }
    }

    /**
     * Get a Brevo contact service for syncing contacts/lists.
     * Returns null if the tenant uses Mailgun or SMTP (no contact sync available).
     */
    public static function getContactService(?array $tenant = null): ?BrevoService {
        $tenant = $tenant ?? TenantResolver::current();
        $provider = $tenant['email_service'] ?? 'kompaza';

        switch ($provider) {
            case 'kompaza':
                $brevo = new BrevoService(defined('BREVO_API_KEY') ? BREVO_API_KEY : null);
                return $brevo->isConfigured() ? $brevo : null;

            case 'brevo':
                $apiKey = $tenant['brevo_api_key'] ?? '';
                if (empty($apiKey)) {
                    return null;
                }
                return new BrevoService($apiKey);

            default:
                return null;
        }
    }
}
