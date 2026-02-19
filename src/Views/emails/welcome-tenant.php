<?php
/**
 * Welcome Email for New Tenant
 * Variables: $userName, $companyName, $subdomain, $loginUrl
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
                    <tr>
                        <td style="background: linear-gradient(135deg, #4f46e5, #3b82f6); padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Welcome to Kompaza!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #1f2937; margin: 0 0 16px;">Hi <?= h($userName ?? '') ?>!</h2>
                            <p style="color: #4b5563; font-size: 16px; line-height: 1.6; margin: 0 0 16px;">
                                Your Kompaza account for <strong><?= h($companyName ?? '') ?></strong> is ready. Here's what you can do:
                            </p>
                            <ul style="color: #4b5563; font-size: 15px; line-height: 1.8; padding-left: 20px; margin: 0 0 24px;">
                                <li>Create lead magnets to capture emails</li>
                                <li>Publish blog articles and ebooks</li>
                                <li>Manage customers and orders</li>
                                <li>Automate LinkedIn outreach with LeadShark</li>
                            </ul>
                            <p style="color: #4b5563; font-size: 16px; line-height: 1.6; margin: 0 0 8px;">
                                Your site: <strong><?= h($subdomain ?? '') ?></strong>
                            </p>
                            <p style="color: #6b7280; font-size: 14px; margin: 0 0 24px;">
                                Your 14-day free trial starts now. No credit card required.
                            </p>
                            <table cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                                <tr>
                                    <td style="background-color: #4f46e5; border-radius: 6px;">
                                        <a href="<?= h($loginUrl ?? '#') ?>" style="display: inline-block; padding: 14px 32px; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 600;">
                                            Go to Your Dashboard
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #9ca3af; font-size: 12px; margin: 0;">
                                &copy; <?= date('Y') ?> Kompaza. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
