-- Migration: Phase 1 Revenue Boosters
-- Order bumps, upsells/downsells, abandoned cart recovery, payment links

-- =============================================
-- 1. ORDER BUMPS
-- =============================================
CREATE TABLE order_bumps (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    product_id INT UNSIGNED NOT NULL COMMENT 'The product being offered as a bump',
    bump_price_dkk DECIMAL(10,2) NOT NULL COMMENT 'Special bump price (can differ from product price)',
    display_text VARCHAR(500) DEFAULT NULL COMMENT 'Checkbox label shown on checkout',
    applies_to ENUM('all','specific_products','category') DEFAULT 'all',
    applies_to_value JSON DEFAULT NULL COMMENT 'Product IDs or category names this bump appears for',
    sort_order INT UNSIGNED DEFAULT 0,
    status ENUM('active','inactive') DEFAULT 'active',
    times_shown INT UNSIGNED DEFAULT 0,
    times_accepted INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- =============================================
-- 2. UPSELL / DOWNSELL OFFERS
-- =============================================
CREATE TABLE upsell_offers (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    headline VARCHAR(500) DEFAULT NULL COMMENT 'Headline shown on upsell page',
    description TEXT COMMENT 'Sales copy for the offer',
    product_id INT UNSIGNED NOT NULL COMMENT 'Product being offered',
    offer_price_dkk DECIMAL(10,2) NOT NULL COMMENT 'Special upsell price',
    original_price_dkk DECIMAL(10,2) DEFAULT NULL COMMENT 'Shown as strikethrough',
    trigger_product_ids JSON DEFAULT NULL COMMENT 'Show after purchasing these products (null=any)',
    offer_type ENUM('upsell','downsell') DEFAULT 'upsell',
    parent_upsell_id INT UNSIGNED DEFAULT NULL COMMENT 'For downsells: which upsell triggers this downsell',
    button_text VARCHAR(100) DEFAULT 'Yes, Add This To My Order!',
    decline_text VARCHAR(100) DEFAULT 'No thanks, I''ll pass',
    image_path VARCHAR(500) DEFAULT NULL,
    sort_order INT UNSIGNED DEFAULT 0,
    status ENUM('active','inactive') DEFAULT 'active',
    times_shown INT UNSIGNED DEFAULT 0,
    times_accepted INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_trigger (tenant_id, offer_type),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_upsell_id) REFERENCES upsell_offers(id) ON DELETE SET NULL
);

-- Track which order items came from upsells/bumps
ALTER TABLE order_items ADD COLUMN source ENUM('cart','order_bump','upsell','downsell') DEFAULT 'cart' AFTER digital_file_path;
ALTER TABLE order_items ADD COLUMN upsell_offer_id INT UNSIGNED DEFAULT NULL AFTER source;
ALTER TABLE order_items ADD COLUMN order_bump_id INT UNSIGNED DEFAULT NULL AFTER upsell_offer_id;

-- =============================================
-- 3. ABANDONED CART RECOVERY
-- =============================================
CREATE TABLE abandoned_carts (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    session_id VARCHAR(255) DEFAULT NULL,
    customer_id INT UNSIGNED DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    customer_name VARCHAR(255) DEFAULT NULL,
    cart_data JSON NOT NULL COMMENT 'Snapshot of cart items',
    subtotal_dkk DECIMAL(10,2) DEFAULT 0.00,
    checkout_started_at TIMESTAMP NULL COMMENT 'When they entered checkout',
    abandoned_at TIMESTAMP NULL COMMENT 'When considered abandoned',
    recovered_at TIMESTAMP NULL COMMENT 'When they completed purchase',
    recovery_order_id INT UNSIGNED DEFAULT NULL,
    emails_sent INT UNSIGNED DEFAULT 0,
    last_email_sent_at TIMESTAMP NULL,
    status ENUM('active','recovered','expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_email (tenant_id, email),
    INDEX idx_abandoned (tenant_id, status, abandoned_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (recovery_order_id) REFERENCES orders(id) ON DELETE SET NULL
);

-- =============================================
-- 4. PAYMENT LINKS
-- =============================================
CREATE TABLE payment_links (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    token VARCHAR(64) NOT NULL COMMENT 'Unique shareable token',
    name VARCHAR(255) NOT NULL COMMENT 'Internal name for admin reference',
    product_id INT UNSIGNED DEFAULT NULL,
    custom_price_dkk DECIMAL(10,2) DEFAULT NULL COMMENT 'Override price (null=use product price)',
    custom_name VARCHAR(255) DEFAULT NULL COMMENT 'Override product name on checkout',
    allow_quantity BOOLEAN DEFAULT FALSE,
    max_uses INT UNSIGNED DEFAULT NULL COMMENT 'null=unlimited',
    used_count INT UNSIGNED DEFAULT 0,
    expires_at TIMESTAMP NULL,
    redirect_url VARCHAR(500) DEFAULT NULL COMMENT 'Custom redirect after purchase',
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_token (token),
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Track payment link usage on orders
ALTER TABLE orders ADD COLUMN payment_link_id INT UNSIGNED DEFAULT NULL AFTER notes;
ALTER TABLE orders ADD COLUMN has_upsells BOOLEAN DEFAULT FALSE AFTER payment_link_id;
