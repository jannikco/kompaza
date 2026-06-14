<?php

namespace App\Models;

use App\Database\Database;

class InvoiceItem {
    public static function create($data) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO invoice_items (invoice_id, description, quantity, unit_price_dkk, total_dkk, sort_order)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['invoice_id'],
            $data['description'],
            $data['quantity'] ?? 1,
            $data['unit_price_dkk'] ?? 0,
            $data['total_dkk'] ?? 0,
            $data['sort_order'] ?? 0,
        ]);
        return $db->lastInsertId();
    }

    public static function allByInvoice($invoiceId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll();
    }

    public static function deleteByInvoice($invoiceId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
        return $stmt->execute([$invoiceId]);
    }
}
