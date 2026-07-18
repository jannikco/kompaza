-- Ebook purchases table (may already exist from 002 on some envs)
CREATE TABLE IF NOT EXISTS ebook_purchases (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    ebook_id INT UNSIGNED NOT NULL,
    customer_email VARCHAR(255) NULL,
    customer_name VARCHAR(255) NULL,
    stripe_checkout_session_id VARCHAR(255) NULL,
    stripe_payment_intent_id VARCHAR(255) NULL,
    amount_cents INT UNSIGNED NOT NULL DEFAULT 0,
    currency VARCHAR(10) NOT NULL DEFAULT 'dkk',
    application_fee_cents INT UNSIGNED NOT NULL DEFAULT 0,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    download_token_id INT UNSIGNED NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_ebook (ebook_id),
    INDEX idx_session (stripe_checkout_session_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
