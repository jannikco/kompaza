<?php

namespace App\Services;

use App\Models\Setting;

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
                return self::createPlatformService();
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
                $platformProvider = Setting::get('platform_email_service', null, 'brevo');
                if ($platformProvider !== 'brevo') {
                    return null;
                }
                $apiKey = Setting::get('platform_brevo_api_key') ?: (defined('BREVO_API_KEY') ? BREVO_API_KEY : null);
                $brevo = new BrevoService($apiKey);
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

    /**
     * Create the platform-level email service based on superadmin settings.
     * Falls back to .env constants if no DB settings exist.
     */
    private static function createPlatformService(): BrevoService|MailgunService|SmtpService {
        $provider = Setting::get('platform_email_service', null, 'brevo');

        switch ($provider) {
            case 'mailgun':
                return new MailgunService(
                    Setting::get('platform_mailgun_api_key') ?: '',
                    Setting::get('platform_mailgun_domain') ?: ''
                );

            case 'smtp':
                return new SmtpService(
                    Setting::get('platform_smtp_host') ?: '',
                    (int)(Setting::get('platform_smtp_port') ?: 587),
                    Setting::get('platform_smtp_username') ?: '',
                    Setting::get('platform_smtp_password') ?: '',
                    Setting::get('platform_smtp_encryption') ?: 'tls'
                );

            case 'brevo':
            default:
                $apiKey = Setting::get('platform_brevo_api_key') ?: (defined('BREVO_API_KEY') ? BREVO_API_KEY : null);
                return new BrevoService($apiKey);
        }
    }
}
