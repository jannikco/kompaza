<?php
/**
 * Order Confirmation Email Template
 * Variables: $customerName, $orderNumber, $items, $subtotal, $tax, $total, $tenantName, $orderUrl
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
                            <h2 style="color: #1f2937; margin: 0 0 8px;">Order Confirmation</h2>
                            <p style="color: #6b7280; margin: 0 0 24px;">Order #<?= h($orderNumber ?? '') ?></p>

                            <p style="color: #4b5563; font-size: 16px; line-height: 1.6; margin: 0 0 24px;">
                                Hi <?= h($customerName ?? '') ?>, thank you for your order! Here's a summary:
                            </p>

                            <!-- Order Items -->
                            <table width="100%" cellpadding="8" cellspacing="0" style="border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 24px;">
                                <thead>
                                    <tr style="background-color: #f9fafb;">
                                        <th style="text-align: left; color: #6b7280; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #e5e7eb;">Item</th>
                                        <th style="text-align: center; color: #6b7280; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #e5e7eb;">Qty</th>
                                        <th style="text-align: right; color: #6b7280; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #e5e7eb;">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($items)): foreach ($items as $item): ?>
                                    <tr>
                                        <td style="color: #1f2937; border-bottom: 1px solid #f3f4f6;"><?= h($item['name']) ?></td>
                                        <td style="color: #4b5563; text-align: center; border-bottom: 1px solid #f3f4f6;"><?= $item['quantity'] ?></td>
                                        <td style="color: #1f2937; text-align: right; border-bottom: 1px solid #f3f4f6;"><?= number_format($item['total_dkk'], 2, ',', '.') ?> DKK</td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" style="text-align: right; color: #6b7280; padding-top: 12px;">Subtotal:</td>
                                        <td style="text-align: right; color: #1f2937; padding-top: 12px;"><?= number_format($subtotal ?? 0, 2, ',', '.') ?> DKK</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align: right; color: #6b7280;">Tax (25%):</td>
                                        <td style="text-align: right; color: #1f2937;"><?= number_format($tax ?? 0, 2, ',', '.') ?> DKK</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align: right; color: #1f2937; font-weight: 700; font-size: 18px; padding-top: 8px;">Total:</td>
                                        <td style="text-align: right; color: #1f2937; font-weight: 700; font-size: 18px; padding-top: 8px;"><?= number_format($total ?? 0, 2, ',', '.') ?> DKK</td>
                                    </tr>
                                </tfoot>
                            </table>

                            <?php if (!empty($orderUrl)): ?>
                            <table cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                                <tr>
                                    <td style="background-color: #4f46e5; border-radius: 6px;">
                                        <a href="<?= h($orderUrl) ?>" style="display: inline-block; padding: 14px 32px; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 600;">
                                            View Order
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <?php endif; ?>
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
