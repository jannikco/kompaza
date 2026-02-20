-- Migration: Add Custom Pages feature
-- Allows tenants to create custom HTML pages (landing pages, marketing pages, etc.)

ALTER TABLE tenants ADD COLUMN feature_custom_pages BOOLEAN DEFAULT FALSE AFTER feature_mastermind;

CREATE TABLE custom_pages (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    slug VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT,
    layout ENUM('shop','full') DEFAULT 'shop',
    meta_description VARCHAR(500),
    status ENUM('draft','published','archived') DEFAULT 'draft',
    is_homepage BOOLEAN DEFAULT FALSE,
    sort_order INT UNSIGNED DEFAULT 0,
    view_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slug_tenant (slug, tenant_id),
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
