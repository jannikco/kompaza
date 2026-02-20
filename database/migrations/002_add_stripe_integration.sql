-- Migration: Add Stripe integration (subscriptions + Connect)
-- Run this on the printworks database

-- Subscription plans
CREATE TABLE subscription_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    stripe_product_id VARCHAR(255) NULL,
    stripe_price_monthly_id VARCHAR(255) NULL,
    stripe_price_annual_id VARCHAR(255) NULL,
    price_monthly_usd INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Price in cents',
    price_annual_usd INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Price in cents (per month)',
    max_customers INT UNSIGNED NULL COMMENT 'NULL = unlimited',
    max_lead_magnets INT UNSIGNED NULL COMMENT 'NULL = unlimited',
    max_products INT UNSIGNED NULL COMMENT 'NULL = unlimited',
    display_order TINYINT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_active_order (is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tenant subscriptions
CREATE TABLE tenant_subscriptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NOT NULL,
    stripe_customer_id VARCHAR(255) NULL,
    stripe_subscription_id VARCHAR(255) NULL UNIQUE,
    billing_interval ENUM('monthly', 'annual') NOT NULL DEFAULT 'monthly',
    status VARCHAR(50) NOT NULL DEFAULT 'trialing',
    trial_ends_at TIMESTAMP NULL,
    current_period_start TIMESTAMP NULL,
    current_period_end TIMESTAMP NULL,
    canceled_at TIMESTAMP NULL,
    cancel_at_period_end TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_stripe_sub (stripe_subscription_id),
    INDEX idx_stripe_customer (stripe_customer_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subscription invoices
CREATE TABLE subscription_invoices (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    stripe_invoice_id VARCHAR(255) NULL UNIQUE,
    stripe_charge_id VARCHAR(255) NULL,
    amount_cents INT UNSIGNED NOT NULL DEFAULT 0,
    currency VARCHAR(10) NOT NULL DEFAULT 'usd',
    status VARCHAR(50) NOT NULL DEFAULT 'draft',
    invoice_url TEXT NULL,
    invoice_pdf TEXT NULL,
    period_start TIMESTAMP NULL,
    period_end TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_stripe_invoice (stripe_invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ebook purchases (via Stripe Connect)
CREATE TABLE ebook_purchases (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    ebook_id INT UNSIGNED NOT NULL,
    customer_email VARCHAR(255) NULL,
    customer_name VARCHAR(255) NULL,
    stripe_checkout_session_id VARCHAR(255) NULL UNIQUE,
    stripe_payment_intent_id VARCHAR(255) NULL,
    amount_cents INT UNSIGNED NOT NULL DEFAULT 0,
    currency VARCHAR(10) NOT NULL DEFAULT 'dkk',
    application_fee_cents INT UNSIGNED NOT NULL DEFAULT 0,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    download_token_id INT UNSIGNED NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_ebook (ebook_id),
    INDEX idx_checkout (stripe_checkout_session_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add Stripe Connect columns to tenants
ALTER TABLE tenants
    ADD COLUMN stripe_connect_id VARCHAR(255) NULL AFTER owner_admin_id,
    ADD COLUMN stripe_connect_onboarded TINYINT(1) NOT NULL DEFAULT 0 AFTER stripe_connect_id,
    ADD COLUMN stripe_connect_charges_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER stripe_connect_onboarded,
    ADD COLUMN stripe_connect_payouts_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER stripe_connect_charges_enabled;

-- Seed default plans
INSERT INTO subscription_plans (name, slug, price_monthly_usd, price_annual_usd, max_customers, max_lead_magnets, max_products, display_order)
VALUES
    ('Starter', 'starter', 7900, 6500, 100, 5, 20, 1),
    ('Growth', 'growth', 14900, 12500, 500, 20, 100, 2),
    ('Enterprise', 'enterprise', 29900, 24900, NULL, NULL, NULL, 3);

-- Seed stripe application fee setting
INSERT INTO settings (setting_key, setting_value) VALUES ('stripe_application_fee_percent', '10')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
