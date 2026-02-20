<?php

namespace App\Services;

use App\Database\Database;

class InvoiceService {

    /**
     * Generate a unique invoice number for a tenant.
     * Format: INV-{YEAR}-{SEQUENCE}
     */
    public static function generateInvoiceNumber($tenantId) {
        $db = Database::getConnection();
        $year = date('Y');
        $prefix = 'INV-' . $year . '-';

        // Find the highest invoice number for this tenant this year
        $stmt = $db->prepare("SELECT invoice_number FROM orders WHERE tenant_id = ? AND invoice_number LIKE ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$tenantId, $prefix . '%']);
        $last = $stmt->fetch();

        if ($last && preg_match('/INV-\d{4}-(\d+)/', $last['invoice_number'], $matches)) {
            $nextNum = (int)$matches[1] + 1;
        } else {
            $nextNum = 1;
        }

        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate invoice due date (default 14 days from now).
     */
    public static function generateDueDate($days = 14) {
        return date('Y-m-d', strtotime("+{$days} days"));
    }

    /**
     * Generate a simple HTML invoice for an order.
     */
    public static function generateInvoiceHtml($order, $orderItems, $tenant) {
        $companyName = $tenant['company_name'] ?? $tenant['name'] ?? 'Company';
        $companyEmail = $tenant['email'] ?? '';
        $companyPhone = $tenant['phone'] ?? '';
        $companyAddress = $tenant['address'] ?? '';
        $cvrNumber = $tenant['cvr_number'] ?? '';
        $currency = $tenant['currency'] ?? 'DKK';

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>';
        $html .= 'body { font-family: Arial, sans-serif; font-size: 14px; color: #333; margin: 40px; }';
        $html .= '.header { display: flex; justify-content: space-between; margin-bottom: 40px; }';
        $html .= '.invoice-title { font-size: 28px; font-weight: bold; color: #111; }';
        $html .= '.meta { margin-bottom: 30px; }';
        $html .= '.meta-row { display: flex; margin-bottom: 5px; }';
        $html .= '.meta-label { font-weight: bold; width: 140px; }';
        $html .= '.addresses { display: flex; gap: 60px; margin-bottom: 30px; }';
        $html .= '.address-block h3 { font-size: 12px; text-transform: uppercase; color: #666; margin-bottom: 8px; }';
        $html .= 'table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }';
        $html .= 'th { background: #f3f4f6; padding: 10px 12px; text-align: left; font-size: 12px; text-transform: uppercase; color: #666; border-bottom: 2px solid #e5e7eb; }';
        $html .= 'td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; }';
        $html .= '.text-right { text-align: right; }';
        $html .= '.totals { margin-left: auto; width: 300px; }';
        $html .= '.totals-row { display: flex; justify-content: space-between; padding: 6px 0; }';
        $html .= '.totals-row.total { font-weight: bold; font-size: 16px; border-top: 2px solid #111; padding-top: 10px; margin-top: 6px; }';
        $html .= '.footer { margin-top: 50px; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #666; }';
        $html .= '</style></head><body>';

        // Header
        $html .= '<div class="header"><div>';
        $html .= '<div class="invoice-title">Invoice</div>';
        $html .= '<div style="color: #666; margin-top: 5px;">' . htmlspecialchars($companyName) . '</div>';
        $html .= '</div></div>';

        // Meta info
        $html .= '<div class="meta">';
        $html .= '<div class="meta-row"><span class="meta-label">Invoice Number:</span><span>' . htmlspecialchars($order['invoice_number'] ?? '') . '</span></div>';
        $html .= '<div class="meta-row"><span class="meta-label">Order Number:</span><span>' . htmlspecialchars($order['order_number']) . '</span></div>';
        $html .= '<div class="meta-row"><span class="meta-label">Date:</span><span>' . date('d/m/Y', strtotime($order['created_at'])) . '</span></div>';
        if (!empty($order['invoice_due_date'])) {
            $html .= '<div class="meta-row"><span class="meta-label">Due Date:</span><span>' . date('d/m/Y', strtotime($order['invoice_due_date'])) . '</span></div>';
        }
        $html .= '<div class="meta-row"><span class="meta-label">Payment Status:</span><span>' . ucfirst($order['payment_status'] ?? 'unpaid') . '</span></div>';
        $html .= '</div>';

        // Addresses
        $html .= '<div class="addresses">';
        $html .= '<div class="address-block"><h3>From</h3>';
        $html .= '<p>' . htmlspecialchars($companyName) . '<br>';
        if ($companyAddress) $html .= htmlspecialchars($companyAddress) . '<br>';
        if ($companyEmail) $html .= htmlspecialchars($companyEmail) . '<br>';
        if ($companyPhone) $html .= htmlspecialchars($companyPhone) . '<br>';
        if ($cvrNumber) $html .= 'CVR: ' . htmlspecialchars($cvrNumber);
        $html .= '</p></div>';

        $html .= '<div class="address-block"><h3>To</h3>';
        $html .= '<p>' . htmlspecialchars($order['customer_name']) . '<br>';
        if (!empty($order['customer_company'])) $html .= htmlspecialchars($order['customer_company']) . '<br>';
        $html .= htmlspecialchars($order['customer_email']) . '<br>';
        if (!empty($order['billing_address_line1'])) $html .= htmlspecialchars($order['billing_address_line1']) . '<br>';
        if (!empty($order['billing_postal_code']) || !empty($order['billing_city'])) {
            $html .= htmlspecialchars(($order['billing_postal_code'] ?? '') . ' ' . ($order['billing_city'] ?? ''));
        }
        $html .= '</p></div></div>';

        // Items table
        $html .= '<table>';
        $html .= '<thead><tr><th>Item</th><th>Qty</th><th class="text-right">Unit Price</th><th class="text-right">Total</th></tr></thead>';
        $html .= '<tbody>';
        foreach ($orderItems as $item) {
            $name = $item['name'] ?? $item['product_name'] ?? 'Item';
            $qty = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price_dkk'] ?? 0;
            $total = $item['total_dkk'] ?? $item['total_price_dkk'] ?? ($unitPrice * $qty);
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($name) . '</td>';
            $html .= '<td>' . $qty . '</td>';
            $html .= '<td class="text-right">' . number_format($unitPrice, 2, ',', '.') . ' ' . $currency . '</td>';
            $html .= '<td class="text-right">' . number_format($total, 2, ',', '.') . ' ' . $currency . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        // Totals
        $html .= '<div class="totals">';
        $html .= '<div class="totals-row"><span>Subtotal</span><span>' . number_format($order['subtotal_dkk'], 2, ',', '.') . ' ' . $currency . '</span></div>';
        if ((float)$order['discount_dkk'] > 0) {
            $html .= '<div class="totals-row"><span>Discount</span><span>-' . number_format($order['discount_dkk'], 2, ',', '.') . ' ' . $currency . '</span></div>';
        }
        $html .= '<div class="totals-row"><span>Tax (25%)</span><span>' . number_format($order['tax_dkk'], 2, ',', '.') . ' ' . $currency . '</span></div>';
        if ((float)$order['shipping_dkk'] > 0) {
            $html .= '<div class="totals-row"><span>Shipping</span><span>' . number_format($order['shipping_dkk'], 2, ',', '.') . ' ' . $currency . '</span></div>';
        }
        $html .= '<div class="totals-row total"><span>Total</span><span>' . number_format($order['total_dkk'], 2, ',', '.') . ' ' . $currency . '</span></div>';
        $html .= '</div>';

        // Footer
        $html .= '<div class="footer">';
        $html .= '<p>Payment terms: Net 14 days. Please reference invoice number <strong>' . htmlspecialchars($order['invoice_number'] ?? '') . '</strong> when making payment.</p>';
        if ($cvrNumber) {
            $html .= '<p>CVR: ' . htmlspecialchars($cvrNumber) . '</p>';
        }
        $html .= '</div>';

        $html .= '</body></html>';
        return $html;
    }

    /**
     * Save invoice HTML as a file and return the path.
     */
    public static function saveInvoicePdf($order, $orderItems, $tenant) {
        $html = self::generateInvoiceHtml($order, $orderItems, $tenant);

        $dir = STORAGE_PATH . '/invoices/' . $tenant['id'];
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = ($order['invoice_number'] ?? 'invoice') . '.html';
        $path = $dir . '/' . $filename;
        file_put_contents($path, $html);

        return 'invoices/' . $tenant['id'] . '/' . $filename;
    }
}
