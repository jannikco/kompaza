-- Migration: Phase 3 Funnel & Marketing
-- Sales funnels, A/B testing, webinar funnels, advanced analytics

-- =============================================
-- 1. SALES FUNNELS
-- =============================================
CREATE TABLE funnels (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    funnel_type ENUM('optin','sales','webinar','launch') NOT NULL DEFAULT 'sales',
    description TEXT DEFAULT NULL,
    status ENUM('draft','active','paused','archived') DEFAULT 'draft',
    total_views INT UNSIGNED DEFAULT 0,
    total_conversions INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_funnel_slug (tenant_id, slug),
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE funnel_steps (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    funnel_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    step_type ENUM('landing_page','sales_page','checkout','upsell','downsell','thank_you','webinar','email_sequence') NOT NULL,
    sort_order INT UNSIGNED DEFAULT 0,
    -- Reference to the actual page/resource
    resource_type VARCHAR(50) DEFAULT NULL COMMENT 'lead_magnet, product, custom_page, email_sequence, webinar',
    resource_id INT UNSIGNED DEFAULT NULL,
    -- Custom URL override
    custom_url VARCHAR(500) DEFAULT NULL,
    -- Step metrics
    views INT UNSIGNED DEFAULT 0,
    conversions INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_funnel_order (funnel_id, sort_order),
    FOREIGN KEY (funnel_id) REFERENCES funnels(id) ON DELETE CASCADE
);

-- =============================================
-- 2. A/B TESTING
-- =============================================
CREATE TABLE ab_tests (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    test_type ENUM('landing_page','custom_page','product_page') NOT NULL DEFAULT 'landing_page',
    -- The original page being tested
    original_type VARCHAR(50) NOT NULL COMMENT 'lead_magnet, custom_page, product',
    original_id INT UNSIGNED NOT NULL,
    status ENUM('draft','running','paused','completed') DEFAULT 'draft',
    winner_variant_id INT UNSIGNED DEFAULT NULL,
    started_at TIMESTAMP NULL,
    ended_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE ab_test_variants (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    ab_test_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL COMMENT 'e.g. "Original", "Variant A", "Variant B"',
    variant_type VARCHAR(50) NOT NULL COMMENT 'lead_magnet, custom_page, product',
    variant_id INT UNSIGNED NOT NULL COMMENT 'ID of the page variant',
    traffic_weight INT UNSIGNED DEFAULT 50 COMMENT 'Percentage of traffic (0-100)',
    views INT UNSIGNED DEFAULT 0,
    conversions INT UNSIGNED DEFAULT 0,
    is_control BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_test (ab_test_id),
    FOREIGN KEY (ab_test_id) REFERENCES ab_tests(id) ON DELETE CASCADE
);

-- =============================================
-- 3. WEBINAR FUNNELS
-- =============================================
CREATE TABLE webinars (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    host_name VARCHAR(255) DEFAULT NULL,
    host_bio TEXT DEFAULT NULL,
    host_image_path VARCHAR(500) DEFAULT NULL,
    -- Scheduling
    webinar_type ENUM('live','replay','evergreen') NOT NULL DEFAULT 'live',
    scheduled_at TIMESTAMP NULL COMMENT 'For live webinars: date/time',
    duration_minutes INT UNSIGNED DEFAULT 60,
    timezone VARCHAR(50) DEFAULT 'Europe/Copenhagen',
    -- Content
    embed_url VARCHAR(500) DEFAULT NULL COMMENT 'YouTube/Zoom/Vimeo embed URL',
    replay_url VARCHAR(500) DEFAULT NULL COMMENT 'Replay video URL after live event',
    -- Registration page
    registration_headline VARCHAR(500) DEFAULT NULL,
    registration_subheadline TEXT DEFAULT NULL,
    registration_cta_text VARCHAR(100) DEFAULT 'Register Now',
    registration_image_path VARCHAR(500) DEFAULT NULL,
    bullet_points JSON DEFAULT NULL COMMENT 'What you will learn bullets',
    -- Post-webinar
    offer_product_id INT UNSIGNED DEFAULT NULL COMMENT 'Product to sell after webinar',
    offer_headline VARCHAR(255) DEFAULT NULL,
    offer_description TEXT DEFAULT NULL,
    -- Email sequences
    reminder_sequence_id INT UNSIGNED DEFAULT NULL,
    followup_sequence_id INT UNSIGNED DEFAULT NULL,
    -- Stats
    registration_count INT UNSIGNED DEFAULT 0,
    attendance_count INT UNSIGNED DEFAULT 0,
    status ENUM('draft','registration_open','live','replay','archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_webinar_slug (tenant_id, slug),
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (offer_product_id) REFERENCES products(id) ON DELETE SET NULL
);

CREATE TABLE webinar_registrations (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    webinar_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    attended BOOLEAN DEFAULT FALSE,
    attended_at TIMESTAMP NULL,
    reminder_sent BOOLEAN DEFAULT FALSE,
    followup_sent BOOLEAN DEFAULT FALSE,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_registration (webinar_id, email),
    INDEX idx_webinar (webinar_id),
    FOREIGN KEY (webinar_id) REFERENCES webinars(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =============================================
-- 4. PAGE ANALYTICS (for advanced dashboard)
-- =============================================
CREATE TABLE page_views (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    page_type VARCHAR(50) NOT NULL COMMENT 'lead_magnet, product, article, ebook, course, custom_page, webinar',
    page_id INT UNSIGNED DEFAULT NULL,
    page_url VARCHAR(500) NOT NULL,
    visitor_id VARCHAR(64) DEFAULT NULL COMMENT 'Anonymous visitor cookie ID',
    user_id INT UNSIGNED DEFAULT NULL,
    referrer VARCHAR(500) DEFAULT NULL,
    utm_source VARCHAR(255) DEFAULT NULL,
    utm_medium VARCHAR(255) DEFAULT NULL,
    utm_campaign VARCHAR(255) DEFAULT NULL,
    device_type ENUM('desktop','mobile','tablet') DEFAULT NULL,
    country_code VARCHAR(5) DEFAULT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant_date (tenant_id, viewed_at),
    INDEX idx_page (tenant_id, page_type, page_id),
    INDEX idx_visitor (tenant_id, visitor_id)
) ENGINE=InnoDB;

-- Monthly aggregation table for fast analytics queries
CREATE TABLE analytics_monthly (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    month_date DATE NOT NULL COMMENT 'First day of the month',
    total_revenue DECIMAL(12,2) DEFAULT 0,
    total_orders INT UNSIGNED DEFAULT 0,
    new_customers INT UNSIGNED DEFAULT 0,
    new_subscribers INT UNSIGNED DEFAULT 0,
    total_page_views INT UNSIGNED DEFAULT 0,
    churned_customers INT UNSIGNED DEFAULT 0,
    UNIQUE KEY unique_month (tenant_id, month_date),
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
