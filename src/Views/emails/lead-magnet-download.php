<?php
/**
 * Lead Magnet Download Email Template
 * Variables: $recipientName, $leadMagnetTitle, $downloadUrl, $tenantName
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #4f46e5, #3b82f6); padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px;"><?= h($tenantName ?? 'Kompaza') ?></h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #1f2937; margin: 0 0 16px;">Hi <?= h($recipientName ?? 'there') ?>!</h2>
                            <p style="color: #4b5563; font-size: 16px; line-height: 1.6; margin: 0 0 24px;">
                                Thank you for your interest in <strong><?= h($leadMagnetTitle ?? '') ?></strong>. Your download is ready!
                            </p>
                            <table cellpadding="0" cellspacing="0" style="margin: 0 auto 24px;">
                                <tr>
                                    <td style="background-color: #4f46e5; border-radius: 6px;">
                                        <a href="<?= h($downloadUrl ?? '#') ?>" style="display: inline-block; padding: 14px 32px; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 600;">
                                            Download Now
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="color: #6b7280; font-size: 14px; line-height: 1.6;">
                                This download link is valid for 7 days and can be used up to 5 times.
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #9ca3af; font-size: 12px; margin: 0;">
                                &copy; <?= date('Y') ?> <?= h($tenantName ?? 'Kompaza') ?>. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
