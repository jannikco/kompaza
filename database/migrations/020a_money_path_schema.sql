-- Migration 020a: Money-path schema reconciliation + webhook idempotency
--
-- Brings `orders` and `order_items` in line with what the application code writes
-- (Order / OrderItem models, checkout-submit, the Stripe webhook), and adds a
-- webhook-event dedupe table. Written defensively (checks information_schema before
-- every change) so it is safe to run on a fresh schema.sql install AND on the existing
-- production database whether or not the columns already exist. Runs before 021.

DROP PROCEDURE IF EXISTS kz_add_col;
DROP PROCEDURE IF EXISTS kz_relax_col;

DELIMITER //
-- Add a column only if it does not already exist
CREATE PROCEDURE kz_add_col(IN tname VARCHAR(64), IN cname VARCHAR(64), IN cdef TEXT)
BEGIN
    IF (SELECT COUNT(*) FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tname AND COLUMN_NAME = cname) = 0 THEN
        SET @ddl = CONCAT('ALTER TABLE `', tname, '` ADD COLUMN `', cname, '` ', cdef);
        PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;
    END IF;
END //
-- Relax a column to a new (nullable) definition only if it currently exists and is NOT NULL
CREATE PROCEDURE kz_relax_col(IN tname VARCHAR(64), IN cname VARCHAR(64), IN cdef TEXT)
BEGIN
    IF (SELECT COUNT(*) FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tname AND COLUMN_NAME = cname AND IS_NULLABLE = 'NO') = 1 THEN
        SET @ddl = CONCAT('ALTER TABLE `', tname, '` MODIFY COLUMN `', cname, '` ', cdef);
        PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;
    END IF;
END //
DELIMITER ;

-- order_items: columns the OrderItem model writes
CALL kz_add_col('order_items', 'product_name',      "VARCHAR(255) NULL");
CALL kz_add_col('order_items', 'product_sku',       "VARCHAR(100) NULL");
CALL kz_add_col('order_items', 'total_price_dkk',   "DECIMAL(10,2) NOT NULL DEFAULT 0.00");
CALL kz_add_col('order_items', 'is_digital',        "TINYINT(1) NOT NULL DEFAULT 0");
CALL kz_add_col('order_items', 'digital_file_path', "VARCHAR(500) NULL");
-- Legacy schema.sql columns are NOT NULL but the model no longer writes them
CALL kz_relax_col('order_items', 'name',      "VARCHAR(255) NULL");
CALL kz_relax_col('order_items', 'total_dkk', "DECIMAL(10,2) NULL DEFAULT 0.00");

-- orders: columns the Order model + checkout write
CALL kz_add_col('orders', 'billing_address',   "TEXT NULL");
CALL kz_add_col('orders', 'shipping_address',  "TEXT NULL");
CALL kz_add_col('orders', 'currency',          "VARCHAR(10) NOT NULL DEFAULT 'DKK'");
CALL kz_add_col('orders', 'payment_reference', "VARCHAR(255) NULL");
CALL kz_add_col('orders', 'paid_at',           "TIMESTAMP NULL");
-- Legacy normalized address columns may be NOT NULL; the model no longer writes them
CALL kz_relax_col('orders', 'shipping_name',          "VARCHAR(255) NULL");
CALL kz_relax_col('orders', 'shipping_address_line1', "VARCHAR(255) NULL");
CALL kz_relax_col('orders', 'shipping_postal_code',   "VARCHAR(20) NULL");
CALL kz_relax_col('orders', 'shipping_city',          "VARCHAR(100) NULL");
CALL kz_relax_col('orders', 'shipping_country',       "VARCHAR(100) NULL");
CALL kz_relax_col('orders', 'billing_name',           "VARCHAR(255) NULL");
CALL kz_relax_col('orders', 'billing_address_line1',  "VARCHAR(255) NULL");
CALL kz_relax_col('orders', 'billing_postal_code',    "VARCHAR(20) NULL");
CALL kz_relax_col('orders', 'billing_city',           "VARCHAR(100) NULL");
CALL kz_relax_col('orders', 'billing_country',        "VARCHAR(100) NULL");

-- Widen orders.status: the legacy ENUM lacks states the checkout/webhook use
-- (e.g. 'awaiting_payment'). VARCHAR preserves existing values and accepts all states.
ALTER TABLE orders MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'pending';

-- Index for fast webhook lookup by payment reference (guarded)
DROP PROCEDURE IF EXISTS kz_add_index;
DELIMITER //
CREATE PROCEDURE kz_add_index(IN tname VARCHAR(64), IN iname VARCHAR(64), IN cols VARCHAR(255))
BEGIN
    IF (SELECT COUNT(*) FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tname AND INDEX_NAME = iname) = 0 THEN
        SET @ddl = CONCAT('CREATE INDEX `', iname, '` ON `', tname, '` (', cols, ')');
        PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;
    END IF;
END //
DELIMITER ;
CALL kz_add_index('orders', 'idx_orders_payment_reference', 'payment_reference');

DROP PROCEDURE IF EXISTS kz_add_col;
DROP PROCEDURE IF EXISTS kz_relax_col;
DROP PROCEDURE IF EXISTS kz_add_index;

-- Webhook idempotency: every processed Stripe event id is recorded once
CREATE TABLE IF NOT EXISTS webhook_events (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    stripe_event_id VARCHAR(255) NOT NULL,
    event_type VARCHAR(100) DEFAULT NULL,
    processed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_stripe_event (stripe_event_id)
) ENGINE=InnoDB;
