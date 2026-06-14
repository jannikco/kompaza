-- Migration: Phase 2 Sales Infrastructure
-- Standalone invoices, payment plans, countdown timers, Stripe Payment Element support

-- =============================================
-- 1. STANDALONE INVOICES
-- =============================================
CREATE TABLE invoices (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    invoice_number VARCHAR(50) NOT NULL,
    order_id INT UNSIGNED DEFAULT NULL COMMENT 'Link to order if invoice was generated from an order',
    customer_id INT UNSIGNED DEFAULT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) DEFAULT NULL,
    customer_company VARCHAR(255) DEFAULT NULL,
    customer_cvr VARCHAR(50) DEFAULT NULL,
    billing_address JSON DEFAULT NULL,
    subtotal_dkk DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax_dkk DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_dkk DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_dkk DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    amount_paid_dkk DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    currency VARCHAR(10) DEFAULT 'DKK',
    tax_rate DECIMAL(5,2) DEFAULT 25.00,
    status ENUM('draft','sent','viewed','paid','partially_paid','overdue','cancelled') DEFAULT 'draft',
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    paid_at TIMESTAMP NULL,
    notes TEXT DEFAULT NULL COMMENT 'Notes shown on the invoice',
    internal_notes TEXT DEFAULT NULL COMMENT 'Private admin notes',
    payment_terms VARCHAR(255) DEFAULT NULL COMMENT 'e.g. Net 14, Net 30',
    footer_text TEXT DEFAULT NULL,
    pdf_path VARCHAR(500) DEFAULT NULL,
    view_token VARCHAR(64) DEFAULT NULL COMMENT 'Token for customer to view invoice online',
    reminder_sent_count INT UNSIGNED DEFAULT 0,
    last_reminder_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_invoice_number (tenant_id, invoice_number),
    UNIQUE KEY unique_view_token (view_token),
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_customer (tenant_id, customer_id),
    INDEX idx_due_date (tenant_id, due_date, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE invoice_items (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT UNSIGNED NOT NULL,
    description VARCHAR(500) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
    unit_price_dkk DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_dkk DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_invoice (invoice_id),
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

CREATE TABLE invoice_payments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT UNSIGNED NOT NULL,
    amount_dkk DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT NULL COMMENT 'bank_transfer, card, cash, etc.',
    payment_reference VARCHAR(255) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    recorded_by INT UNSIGNED DEFAULT NULL COMMENT 'Admin user who recorded this payment',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_invoice (invoice_id),
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =============================================
-- 2. PAYMENT PLANS / INSTALLMENTS
-- =============================================
ALTER TABLE products ADD COLUMN payment_plan_enabled BOOLEAN DEFAULT FALSE AFTER digital_file_path;
ALTER TABLE products ADD COLUMN installment_count INT UNSIGNED DEFAULT NULL AFTER payment_plan_enabled;
ALTER TABLE products ADD COLUMN installment_price_dkk DECIMAL(10,2) DEFAULT NULL AFTER installment_count;
ALTER TABLE products ADD COLUMN trial_days INT UNSIGNED DEFAULT NULL AFTER installment_price_dkk;

-- Track installment orders
ALTER TABLE orders ADD COLUMN payment_plan_type ENUM('full','installment') DEFAULT 'full' AFTER has_upsells;
ALTER TABLE orders ADD COLUMN installment_count INT UNSIGNED DEFAULT NULL AFTER payment_plan_type;
ALTER TABLE orders ADD COLUMN installments_paid INT UNSIGNED DEFAULT 0 AFTER installment_count;
ALTER TABLE orders ADD COLUMN stripe_subscription_id VARCHAR(255) DEFAULT NULL AFTER installments_paid;
ALTER TABLE orders ADD COLUMN next_payment_date DATE DEFAULT NULL AFTER stripe_subscription_id;

-- =============================================
-- 3. COUNTDOWN TIMERS
-- =============================================
CREATE TABLE countdown_timers (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL COMMENT 'Internal name for admin reference',
    timer_type ENUM('fixed','evergreen') NOT NULL DEFAULT 'fixed',
    headline VARCHAR(255) DEFAULT NULL COMMENT 'Text shown above timer',
    subheadline VARCHAR(500) DEFAULT NULL COMMENT 'Text shown below timer',
    end_date TIMESTAMP NULL COMMENT 'For fixed timers: when the timer expires',
    duration_minutes INT UNSIGNED DEFAULT NULL COMMENT 'For evergreen timers: duration per visitor',
    redirect_url VARCHAR(500) DEFAULT NULL COMMENT 'Where to redirect when timer expires',
    expired_action ENUM('redirect','hide','show_message') DEFAULT 'hide',
    expired_message TEXT DEFAULT NULL COMMENT 'Message shown when timer expires (if action=show_message)',
    style_preset ENUM('default','urgent','minimal','banner') DEFAULT 'default',
    bg_color VARCHAR(20) DEFAULT '#111827',
    text_color VARCHAR(20) DEFAULT '#FFFFFF',
    accent_color VARCHAR(20) DEFAULT '#EF4444',
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- =============================================
-- 4. STRIPE PAYMENT ELEMENT SUPPORT
-- =============================================
-- Add payment_method_types to orders for tracking which payment method was actually used
ALTER TABLE orders MODIFY COLUMN payment_method ENUM('invoice','card','klarna','mobilepay','applepay','googlepay') DEFAULT 'invoice';
