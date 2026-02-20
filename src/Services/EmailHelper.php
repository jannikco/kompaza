<?php

namespace App\Services;

use App\Models\Setting;

class EmailHelper {

    /**
     * Build lead magnet email subject and HTML content.
     *
     * @return array{subject: string, htmlContent: string}
     */
    public static function buildLeadMagnetEmail(string $email, string $name, array $leadMagnet, string $downloadUrl): array {
        $subject = $leadMagnet['email_subject'] ?? 'Din gratis download: ' . ($leadMagnet['title'] ?? 'Lead Magnet');

        $htmlContent = $leadMagnet['email_body_html'] ?? '';

        if (empty($htmlContent)) {
            $firstName = explode(' ', trim($name), 2)[0] ?: 'der';
            $title = htmlspecialchars($leadMagnet['title'] ?? 'Download');
            $htmlContent = self::buildLeadMagnetTemplate($firstName, $title, $downloadUrl);
        } else {
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

        return ['subject' => $subject, 'htmlContent' => $htmlContent];
    }

    /**
     * Resolve sender email from tenant setting or platform default.
     */
    public static function resolveFromEmail(): string {
        $tenant = TenantResolver::current();
        if ($tenant) {
            $tenantEmail = Setting::get('mail_from_address', $tenant['id']);
            if (!empty($tenantEmail)) {
                return $tenantEmail;
            }
            if (!empty($tenant['email'])) {
                return $tenant['email'];
            }
        }
        return defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : 'info@kompaza.com';
    }

    /**
     * Resolve sender name from tenant setting or platform default.
     */
    public static function resolveFromName(): string {
        $tenant = TenantResolver::current();
        if ($tenant) {
            $tenantName = Setting::get('mail_from_name', $tenant['id']);
            if (!empty($tenantName)) {
                return $tenantName;
            }
            if (!empty($tenant['company_name'])) {
                return $tenant['company_name'];
            }
        }
        return defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'Kompaza';
    }

    /**
     * Build a default HTML email template for lead magnet delivery.
     */
    private static function buildLeadMagnetTemplate(string $firstName, string $title, string $downloadUrl): string {
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
