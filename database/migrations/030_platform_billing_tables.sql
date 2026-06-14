-- Migration 030: Platform billing tables (idempotent)
--
-- subscription_plans / tenant_subscriptions / subscription_invoices power the platform's
-- tenant-subscription billing and the superadmin Billing/Analytics/Subscriptions views.
-- They were defined in migration 002 but never applied to production. These are the exact
-- definitions from 002 with IF NOT EXISTS so this is safe to run anywhere.

CREATE TABLE IF NOT EXISTS subscription_plans (
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

CREATE TABLE IF NOT EXISTS tenant_subscriptions (
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

CREATE TABLE IF NOT EXISTS subscription_invoices (
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
