<?php

namespace App\Models;

use App\Database\Database;

class Invoice {
    public static function find($id, $tenantId = null) {
        $db = Database::getConnection();
        if ($tenantId) {
            $stmt = $db->prepare("SELECT * FROM invoices WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $tenantId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM invoices WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }

    public static function findByToken($token) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT i.*, t.company_name as tenant_company, t.email as tenant_email,
                   t.phone as tenant_phone, t.address as tenant_address, t.cvr_number as tenant_cvr,
                   t.currency as tenant_currency, t.slug as tenant_slug
            FROM invoices i
            JOIN tenants t ON t.id = i.tenant_id
            WHERE i.view_token = ?
        ");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public static function allByTenant($tenantId, $status = null, $limit = 50, $offset = 0) {
        $db = Database::getConnection();
        $sql = "SELECT i.*, u.name as customer_user_name FROM invoices i LEFT JOIN users u ON u.id = i.customer_id WHERE i.tenant_id = ?";
        $params = [$tenantId];

        if ($status) {
            $sql .= " AND i.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY i.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO invoices (tenant_id, invoice_number, order_id, customer_id, customer_name, customer_email, customer_phone, customer_company, customer_cvr, billing_address, subtotal_dkk, tax_dkk, discount_dkk, total_dkk, currency, tax_rate, status, issue_date, due_date, notes, internal_notes, payment_terms, footer_text, view_token)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['tenant_id'],
            $data['invoice_number'],
            $data['order_id'] ?? null,
            $data['customer_id'] ?? null,
            $data['customer_name'],
            $data['customer_email'],
            $data['customer_phone'] ?? null,
            $data['customer_company'] ?? null,
            $data['customer_cvr'] ?? null,
            $data['billing_address'] ?? null,
            $data['subtotal_dkk'] ?? 0,
            $data['tax_dkk'] ?? 0,
            $data['discount_dkk'] ?? 0,
            $data['total_dkk'] ?? 0,
            $data['currency'] ?? 'DKK',
            $data['tax_rate'] ?? 25.00,
            $data['status'] ?? 'draft',
            $data['issue_date'],
            $data['due_date'],
            $data['notes'] ?? null,
            $data['internal_notes'] ?? null,
            $data['payment_terms'] ?? null,
            $data['footer_text'] ?? null,
            $data['view_token'] ?? bin2hex(random_bytes(16)),
        ]);
        return $db->lastInsertId();
    }

    public static function update($id, $data) {
        $db = Database::getConnection();
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;
        $stmt = $db->prepare("UPDATE invoices SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    public static function delete($id, $tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM invoices WHERE id = ? AND tenant_id = ? AND status = 'draft'");
        return $stmt->execute([$id, $tenantId]);
    }

    public static function generateNumber($tenantId) {
        $db = Database::getConnection();
        $year = date('Y');
        $prefix = 'INV-' . $year . '-';

        // Check both orders and invoices tables for the highest number
        $stmt = $db->prepare("
            SELECT invoice_number FROM (
                SELECT invoice_number FROM orders WHERE tenant_id = ? AND invoice_number LIKE ?
                UNION ALL
                SELECT invoice_number FROM invoices WHERE tenant_id = ? AND invoice_number LIKE ?
            ) combined ORDER BY invoice_number DESC LIMIT 1
        ");
        $stmt->execute([$tenantId, $prefix . '%', $tenantId, $prefix . '%']);
        $last = $stmt->fetch();

        if ($last && preg_match('/INV-\d{4}-(\d+)/', $last['invoice_number'], $matches)) {
            $nextNum = (int)$matches[1] + 1;
        } else {
            $nextNum = 1;
        }

        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    public static function recordPayment($invoiceId, $amount, $method = null, $reference = null, $notes = null, $recordedBy = null) {
        $db = Database::getConnection();

        $stmt = $db->prepare("
            INSERT INTO invoice_payments (invoice_id, amount_dkk, payment_method, payment_reference, notes, recorded_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$invoiceId, $amount, $method, $reference, $notes, $recordedBy]);

        // Update amount_paid on invoice
        $stmt = $db->prepare("UPDATE invoices SET amount_paid_dkk = amount_paid_dkk + ? WHERE id = ?");
        $stmt->execute([$amount, $invoiceId]);

        // Check if fully paid
        $invoice = self::find($invoiceId);
        if ($invoice && (float)$invoice['amount_paid_dkk'] >= (float)$invoice['total_dkk']) {
            self::update($invoiceId, ['status' => 'paid', 'paid_at' => date('Y-m-d H:i:s')]);
        } else {
            self::update($invoiceId, ['status' => 'partially_paid']);
        }

        return $db->lastInsertId();
    }

    public static function getPayments($invoiceId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT ip.*, u.name as recorded_by_name FROM invoice_payments ip LEFT JOIN users u ON u.id = ip.recorded_by WHERE ip.invoice_id = ? ORDER BY ip.paid_at DESC");
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll();
    }

    public static function countByTenant($tenantId, $status = null) {
        $db = Database::getConnection();
        if ($status) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM invoices WHERE tenant_id = ? AND status = ?");
            $stmt->execute([$tenantId, $status]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM invoices WHERE tenant_id = ?");
            $stmt->execute([$tenantId]);
        }
        return $stmt->fetch()['count'];
    }

    public static function totalOutstanding($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COALESCE(SUM(total_dkk - amount_paid_dkk), 0) as total FROM invoices WHERE tenant_id = ? AND status IN ('sent','viewed','partially_paid','overdue')");
        $stmt->execute([$tenantId]);
        return $stmt->fetch()['total'];
    }

    public static function getOverdue($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM invoices WHERE tenant_id = ? AND status IN ('sent','viewed','partially_paid') AND due_date < CURDATE() ORDER BY due_date ASC");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function markOverdue($tenantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE invoices SET status = 'overdue' WHERE tenant_id = ? AND status IN ('sent','viewed') AND due_date < CURDATE()");
        $stmt->execute([$tenantId]);
        return $stmt->rowCount();
    }

    public static function markViewed($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE invoices SET status = 'viewed' WHERE id = ? AND status = 'sent'");
        return $stmt->execute([$id]);
    }
}
