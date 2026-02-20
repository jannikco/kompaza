-- ============================================
-- KOMPAZA.COM - Multi-Tenant SaaS Platform
-- Database Schema
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- PHASE 1: MULTI-TENANT CORE
-- ============================================

CREATE TABLE IF NOT EXISTS tenants (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    custom_domain VARCHAR(255) UNIQUE DEFAULT NULL,
    status ENUM('trial','active','suspended','cancelled') DEFAULT 'trial',
    owner_user_id INT UNSIGNED DEFAULT NULL,

    -- Branding
    logo_url VARCHAR(500) DEFAULT NULL,
    favicon_url VARCHAR(500) DEFAULT NULL,
    primary_color VARCHAR(7) DEFAULT '#3b82f6',
    secondary_color VARCHAR(7) DEFAULT '#6366f1',
    company_name VARCHAR(255) DEFAULT NULL,
    tagline VARCHAR(500) DEFAULT NULL,

    -- Contact
    email VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    cvr_number VARCHAR(20) DEFAULT NULL,

    -- Pricing
    currency VARCHAR(3) DEFAULT 'DKK',
    tax_rate DECIMAL(5,2) DEFAULT 25.00,

    -- Integrations (NULL = use platform default)
    brevo_api_key VARCHAR(255) DEFAULT NULL,
    brevo_list_id VARCHAR(50) DEFAULT NULL,
    stripe_publishable_key VARCHAR(255) DEFAULT NULL,
    stripe_secret_key VARCHAR(255) DEFAULT NULL,
    stripe_webhook_secret VARCHAR(255) DEFAULT NULL,
    google_analytics_id VARCHAR(50) DEFAULT NULL,

    -- Email service provider selection
    email_service ENUM('kompaza','brevo','mailgun','smtp') DEFAULT 'kompaza',
    mailgun_api_key VARCHAR(255) DEFAULT NULL,
    mailgun_domain VARCHAR(255) DEFAULT NULL,
    smtp_host VARCHAR(255) DEFAULT NULL,
    smtp_port SMALLINT UNSIGNED DEFAULT 587,
    smtp_username VARCHAR(255) DEFAULT NULL,
    smtp_password VARCHAR(255) DEFAULT NULL,
    smtp_encryption ENUM('tls','ssl','none') DEFAULT 'tls',

    -- Features
    feature_blog BOOLEAN DEFAULT TRUE,
    feature_ebooks BOOLEAN DEFAULT TRUE,
    feature_lead_magnets BOOLEAN DEFAULT TRUE,
    feature_orders BOOLEAN DEFAULT TRUE,
    feature_connectpilot BOOLEAN DEFAULT FALSE,
    custom_css TEXT DEFAULT NULL,
    custom_footer_html TEXT DEFAULT NULL,

    -- Subscription
    plan_id INT UNSIGNED DEFAULT NULL,
    trial_ends_at TIMESTAMP NULL,
    subscription_status ENUM('trialing','active','past_due','cancelled') DEFAULT 'trialing',
    stripe_customer_id VARCHAR(255) DEFAULT NULL,
    stripe_subscription_id VARCHAR(255) DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS tenant_domains (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    domain VARCHAR(255) NOT NULL UNIQUE,
    ssl_status ENUM('pending','active','failed') DEFAULT 'pending',
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS plans (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    price_monthly_usd DECIMAL(10,2) NOT NULL DEFAULT 0,
    price_yearly_usd DECIMAL(10,2) DEFAULT NULL,
    max_customers INT UNSIGNED DEFAULT NULL,
    max_leads INT UNSIGNED DEFAULT NULL,
    max_campaigns INT UNSIGNED DEFAULT NULL,
    max_products INT UNSIGNED DEFAULT NULL,
    max_lead_magnets INT UNSIGNED DEFAULT NULL,
    features_json JSON DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- USERS (all roles in one table)
-- ============================================

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED DEFAULT NULL,
    role ENUM('superadmin','tenant_admin','customer') NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    company VARCHAR(255) DEFAULT NULL,
    address_line1 VARCHAR(255) DEFAULT NULL,
    address_line2 VARCHAR(255) DEFAULT NULL,
    postal_code VARCHAR(20) DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    country VARCHAR(2) DEFAULT 'DK',
    cvr_number VARCHAR(20) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    tags JSON DEFAULT NULL,
    last_login_at TIMESTAMP NULL,
    email_verified_at TIMESTAMP NULL,
    status ENUM('active','inactive','banned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email_tenant (email, tenant_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_role (role),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- SETTINGS (per-tenant)
-- ============================================

CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED DEFAULT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT DEFAULT NULL,
    setting_type ENUM('text','textarea','boolean','json','number','password') DEFAULT 'text',
    description VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_key_tenant (setting_key, tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- ============================================
-- AUDIT & RATE LIMITING
-- ============================================

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED DEFAULT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) DEFAULT NULL,
    entity_id INT UNSIGNED DEFAULT NULL,
    payload JSON DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
);

CREATE TABLE IF NOT EXISTS rate_limits (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    identifier VARCHAR(255) NOT NULL,
    action VARCHAR(100) NOT NULL,
    attempts INT UNSIGNED DEFAULT 1,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_identifier_action (identifier, action),
    INDEX idx_last_attempt (last_attempt)
);

-- ============================================
-- PHASE 2: CONTENT MANAGEMENT
-- ============================================

CREATE TABLE IF NOT EXISTS lead_magnets (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    slug VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    subtitle TEXT DEFAULT NULL,
    meta_description VARCHAR(500) DEFAULT NULL,
    hero_headline VARCHAR(500) DEFAULT NULL,
    hero_subheadline TEXT DEFAULT NULL,
    hero_cta_text VARCHAR(100) DEFAULT 'Download Free',
    hero_bg_color VARCHAR(7) DEFAULT '#1e40af',
    hero_image_path VARCHAR(500) DEFAULT NULL,
    features_headline VARCHAR(255) DEFAULT NULL,
    features JSON DEFAULT NULL,
    pdf_filename VARCHAR(255) DEFAULT NULL,
    pdf_original_name VARCHAR(255) DEFAULT NULL,
    email_subject VARCHAR(255) DEFAULT NULL,
    email_body_html TEXT DEFAULT NULL,
    brevo_list_id VARCHAR(50) DEFAULT NULL,
    status ENUM('draft','published','archived') DEFAULT 'draft',
    view_count INT UNSIGNED DEFAULT 0,
    signup_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slug_tenant (slug, tenant_id),
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS articles (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    slug VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    excerpt TEXT DEFAULT NULL,
    content LONGTEXT DEFAULT NULL,
    featured_image VARCHAR(500) DEFAULT NULL,
    meta_description VARCHAR(500) DEFAULT NULL,
    category VARCHAR(100) DEFAULT NULL,
    tags JSON DEFAULT NULL,
    status ENUM('draft','published','archived') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    author_name VARCHAR(255) DEFAULT NULL,
    view_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slug_tenant (slug, tenant_id),
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ebooks (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    slug VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(500) DEFAULT NULL,
    description LONGTEXT DEFAULT NULL,
    hero_headline VARCHAR(500) DEFAULT NULL,
    hero_subheadline TEXT DEFAULT NULL,
    cover_image_path VARCHAR(500) DEFAULT NULL,
    features JSON DEFAULT NULL,
    pdf_filename VARCHAR(255) DEFAULT NULL,
    pdf_original_name VARCHAR(255) DEFAULT NULL,
    page_count INT UNSIGNED DEFAULT NULL,
    price_dkk DECIMAL(10,2) DEFAULT 0.00,
    meta_description VARCHAR(500) DEFAULT NULL,
    status ENUM('draft','published','archived') DEFAULT 'draft',
    view_count INT UNSIGNED DEFAULT 0,
    download_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slug_tenant (slug, tenant_id),
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS email_signups (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(255) DEFAULT NULL,
    source_type ENUM('lead_magnet','ebook','newsletter','article','waitlist') NOT NULL,
    source_id INT UNSIGNED DEFAULT NULL,
    source_slug VARCHAR(255) DEFAULT NULL,
    brevo_synced BOOLEAN DEFAULT FALSE,
    brevo_synced_at TIMESTAMP NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_email (email),
    INDEX idx_created (created_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS download_tokens (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    source_type ENUM('lead_magnet','ebook') NOT NULL,
    source_id INT UNSIGNED NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    downloads INT UNSIGNED DEFAULT 0,
    max_downloads INT UNSIGNED DEFAULT 5,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- ============================================
-- PHASE 3: CUSTOMER MANAGEMENT
-- ============================================

CREATE TABLE IF NOT EXISTS customer_addresses (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    label VARCHAR(100) DEFAULT 'Default',
    is_default BOOLEAN DEFAULT FALSE,
    name VARCHAR(255) NOT NULL,
    company VARCHAR(255) DEFAULT NULL,
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255) DEFAULT NULL,
    postal_code VARCHAR(20) NOT NULL,
    city VARCHAR(100) NOT NULL,
    country VARCHAR(2) DEFAULT 'DK',
    phone VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant_user (tenant_id, user_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS customer_tags (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) DEFAULT '#3b82f6',
    UNIQUE KEY unique_name_tenant (name, tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS customer_tag_assignments (
    customer_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (customer_id, tag_id),
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES customer_tags(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS customer_notes (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    author_id INT UNSIGNED NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- PHASE 4: ORDER MANAGEMENT
-- ============================================

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    slug VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description LONGTEXT DEFAULT NULL,
    short_description TEXT DEFAULT NULL,
    image_path VARCHAR(500) DEFAULT NULL,
    gallery JSON DEFAULT NULL,
    price_dkk DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    compare_price_dkk DECIMAL(10,2) DEFAULT NULL,
    sku VARCHAR(100) DEFAULT NULL,
    stock_quantity INT DEFAULT NULL,
    track_stock BOOLEAN DEFAULT FALSE,
    category VARCHAR(100) DEFAULT NULL,
    tags JSON DEFAULT NULL,
    is_digital BOOLEAN DEFAULT FALSE,
    digital_file_path VARCHAR(500) DEFAULT NULL,
    status ENUM('draft','published','archived') DEFAULT 'draft',
    sort_order INT DEFAULT 0,
    view_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slug_tenant (slug, tenant_id),
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    order_number VARCHAR(50) NOT NULL,
    customer_id INT UNSIGNED DEFAULT NULL,
    status ENUM('pending','paid','processing','shipped','delivered','cancelled','refunded') DEFAULT 'pending',

    -- Customer info (snapshot)
    customer_email VARCHAR(255) NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) DEFAULT NULL,
    customer_company VARCHAR(255) DEFAULT NULL,

    -- Shipping address (snapshot)
    shipping_name VARCHAR(255) DEFAULT NULL,
    shipping_address_line1 VARCHAR(255) DEFAULT NULL,
    shipping_address_line2 VARCHAR(255) DEFAULT NULL,
    shipping_postal_code VARCHAR(20) DEFAULT NULL,
    shipping_city VARCHAR(100) DEFAULT NULL,
    shipping_country VARCHAR(2) DEFAULT 'DK',

    -- Billing address (snapshot)
    billing_name VARCHAR(255) DEFAULT NULL,
    billing_address_line1 VARCHAR(255) DEFAULT NULL,
    billing_address_line2 VARCHAR(255) DEFAULT NULL,
    billing_postal_code VARCHAR(20) DEFAULT NULL,
    billing_city VARCHAR(100) DEFAULT NULL,
    billing_country VARCHAR(2) DEFAULT 'DK',

    -- Totals
    subtotal_dkk DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax_dkk DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    shipping_dkk DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_dkk DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_dkk DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    -- Payment
    payment_method ENUM('stripe','invoice','mobilepay') DEFAULT NULL,
    payment_status ENUM('unpaid','paid','refunded','partial_refund') DEFAULT 'unpaid',
    stripe_payment_intent_id VARCHAR(255) DEFAULT NULL,
    paid_at TIMESTAMP NULL,

    -- Tracking
    tracking_number VARCHAR(255) DEFAULT NULL,
    tracking_url VARCHAR(500) DEFAULT NULL,
    shipped_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,

    notes TEXT DEFAULT NULL,
    internal_notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_order_tenant (order_number, tenant_id),
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_customer (customer_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) DEFAULT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    unit_price_dkk DECIMAL(10,2) NOT NULL,
    total_dkk DECIMAL(10,2) NOT NULL,
    options JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS order_status_history (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_id INT UNSIGNED NOT NULL,
    status VARCHAR(50) NOT NULL,
    note TEXT DEFAULT NULL,
    changed_by INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS carts (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    session_id VARCHAR(255) DEFAULT NULL,
    customer_id INT UNSIGNED DEFAULT NULL,
    items JSON NOT NULL DEFAULT ('[]'),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_customer (customer_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- ============================================
-- PHASE 5: CONNECTPILOT - LINKEDIN AUTOMATION
-- ============================================

CREATE TABLE IF NOT EXISTS linkedin_accounts (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    linkedin_name VARCHAR(255) DEFAULT NULL,
    linkedin_email VARCHAR(255) DEFAULT NULL,
    linkedin_profile_url VARCHAR(500) DEFAULT NULL,
    li_at_cookie TEXT DEFAULT NULL,
    csrf_token VARCHAR(255) DEFAULT NULL,
    cookie_expires_at TIMESTAMP NULL,
    status ENUM('active','paused','disconnected','cookie_expired') DEFAULT 'disconnected',
    daily_connection_limit INT DEFAULT 20,
    daily_message_limit INT DEFAULT 50,
    connections_sent_today INT DEFAULT 0,
    messages_sent_today INT DEFAULT 0,
    last_activity_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS connectpilot_campaigns (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    linkedin_account_id INT UNSIGNED DEFAULT NULL,
    status ENUM('draft','active','paused','completed') DEFAULT 'draft',
    target_audience TEXT DEFAULT NULL,
    search_url VARCHAR(2000) DEFAULT NULL,

    -- Stats
    leads_found INT UNSIGNED DEFAULT 0,
    connections_sent INT UNSIGNED DEFAULT 0,
    connections_accepted INT UNSIGNED DEFAULT 0,
    messages_sent INT UNSIGNED DEFAULT 0,
    replies_received INT UNSIGNED DEFAULT 0,

    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (linkedin_account_id) REFERENCES linkedin_accounts(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS connectpilot_sequence_steps (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    campaign_id INT UNSIGNED NOT NULL,
    step_number INT UNSIGNED NOT NULL,
    action_type ENUM('connect','message','follow_up','like_post','view_profile') NOT NULL,
    message_template TEXT DEFAULT NULL,
    delay_days INT UNSIGNED DEFAULT 1,
    delay_hours INT UNSIGNED DEFAULT 0,
    condition_type ENUM('always','if_accepted','if_no_reply','if_replied') DEFAULT 'always',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_campaign (campaign_id),
    FOREIGN KEY (campaign_id) REFERENCES connectpilot_campaigns(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS linkedin_leads (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    campaign_id INT UNSIGNED DEFAULT NULL,
    linkedin_profile_url VARCHAR(500) DEFAULT NULL,
    linkedin_id VARCHAR(100) DEFAULT NULL,
    first_name VARCHAR(255) DEFAULT NULL,
    last_name VARCHAR(255) DEFAULT NULL,
    headline VARCHAR(500) DEFAULT NULL,
    company VARCHAR(255) DEFAULT NULL,
    job_title VARCHAR(255) DEFAULT NULL,
    location VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    profile_image_url VARCHAR(500) DEFAULT NULL,

    -- Status tracking
    connection_status ENUM('none','pending','accepted','rejected') DEFAULT 'none',
    current_step INT UNSIGNED DEFAULT 0,
    score INT DEFAULT 0,
    tags JSON DEFAULT NULL,

    -- Linked customer (if converted)
    customer_id INT UNSIGNED DEFAULT NULL,
    converted_at TIMESTAMP NULL,

    last_contacted_at TIMESTAMP NULL,
    last_replied_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_campaign (campaign_id),
    INDEX idx_status (connection_status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (campaign_id) REFERENCES connectpilot_campaigns(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS connectpilot_activity_log (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    campaign_id INT UNSIGNED DEFAULT NULL,
    lead_id INT UNSIGNED DEFAULT NULL,
    action_type VARCHAR(50) NOT NULL,
    step_id INT UNSIGNED DEFAULT NULL,
    message_sent TEXT DEFAULT NULL,
    response TEXT DEFAULT NULL,
    status ENUM('success','failed','skipped') NOT NULL,
    error_message TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_campaign (campaign_id),
    INDEX idx_lead (lead_id),
    INDEX idx_created (created_at)
);

-- ============================================
-- PHASE 6: COURSE PLATFORM
-- ============================================

CREATE TABLE IF NOT EXISTS courses (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    slug VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(500) DEFAULT NULL,
    description LONGTEXT DEFAULT NULL,
    short_description TEXT DEFAULT NULL,
    cover_image_path VARCHAR(500) DEFAULT NULL,
    promo_video_s3_key VARCHAR(500) DEFAULT NULL,
    pricing_type ENUM('free','one_time','subscription') DEFAULT 'free',
    price_dkk DECIMAL(10,2) DEFAULT NULL,
    compare_price_dkk DECIMAL(10,2) DEFAULT NULL,
    subscription_price_monthly_dkk DECIMAL(10,2) DEFAULT NULL,
    subscription_price_yearly_dkk DECIMAL(10,2) DEFAULT NULL,
    stripe_monthly_price_id VARCHAR(255) DEFAULT NULL,
    stripe_yearly_price_id VARCHAR(255) DEFAULT NULL,
    status ENUM('draft','published','archived') DEFAULT 'draft',
    is_featured BOOLEAN DEFAULT FALSE,
    drip_enabled BOOLEAN DEFAULT FALSE,
    drip_interval_days INT UNSIGNED DEFAULT NULL,
    instructor_name VARCHAR(255) DEFAULT NULL,
    instructor_bio TEXT DEFAULT NULL,
    instructor_image_path VARCHAR(500) DEFAULT NULL,
    view_count INT UNSIGNED DEFAULT 0,
    enrollment_count INT UNSIGNED DEFAULT 0,
    total_duration_seconds INT UNSIGNED DEFAULT 0,
    total_lessons INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slug_tenant (slug, tenant_id),
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS course_modules (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    course_id INT UNSIGNED NOT NULL,
    tenant_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    sort_order INT UNSIGNED DEFAULT 0,
    drip_days_after_enrollment INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_course_sort (course_id, sort_order),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS course_lessons (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    module_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    tenant_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) DEFAULT NULL,
    lesson_type ENUM('video','text','video_text','download') DEFAULT 'video',
    text_content LONGTEXT DEFAULT NULL,
    video_s3_key VARCHAR(500) DEFAULT NULL,
    video_original_filename VARCHAR(255) DEFAULT NULL,
    video_duration_seconds INT UNSIGNED DEFAULT NULL,
    video_file_size_bytes BIGINT UNSIGNED DEFAULT NULL,
    video_thumbnail_s3_key VARCHAR(500) DEFAULT NULL,
    video_status ENUM('pending','uploading','transcoding','ready','failed') DEFAULT NULL,
    video_error_message TEXT DEFAULT NULL,
    video_variants JSON DEFAULT NULL,
    resources JSON DEFAULT NULL,
    is_preview BOOLEAN DEFAULT FALSE,
    sort_order INT UNSIGNED DEFAULT 0,
    drip_days_after_enrollment INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_module_sort (module_id, sort_order),
    INDEX idx_course (course_id),
    FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS course_enrollments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    enrollment_source ENUM('purchase','subscription','manual','free') DEFAULT 'free',
    order_id INT UNSIGNED DEFAULT NULL,
    stripe_subscription_id VARCHAR(255) DEFAULT NULL,
    status ENUM('active','expired','cancelled','refunded') DEFAULT 'active',
    completed_lessons INT UNSIGNED DEFAULT 0,
    total_lessons INT UNSIGNED DEFAULT 0,
    progress_percent DECIMAL(5,2) DEFAULT 0.00,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    last_accessed_at TIMESTAMP NULL,
    UNIQUE KEY unique_course_user (course_id, user_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_user (user_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS course_progress (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    enrollment_id INT UNSIGNED NOT NULL,
    lesson_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    video_position_seconds INT UNSIGNED DEFAULT 0,
    video_watched_percent DECIMAL(5,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_enrollment_lesson (enrollment_id, lesson_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (enrollment_id) REFERENCES course_enrollments(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS video_transcode_jobs (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    lesson_id INT UNSIGNED NOT NULL,
    source_local_path VARCHAR(500) NOT NULL,
    status ENUM('pending','processing','uploading','completed','failed') DEFAULT 'pending',
    error_message TEXT DEFAULT NULL,
    output_s3_key VARCHAR(500) DEFAULT NULL,
    output_variants JSON DEFAULT NULL,
    duration_seconds INT UNSIGNED DEFAULT NULL,
    thumbnail_s3_key VARCHAR(500) DEFAULT NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE
);

-- ============================================
-- PHASE 7: QUIZZES, CERTIFICATES, ATTACHMENTS,
-- PASSWORD RESETS, CONTACT MESSAGES
-- ============================================

CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(100) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
);

CREATE TABLE IF NOT EXISTS quizzes (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    module_id INT UNSIGNED DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    pass_threshold DECIMAL(5,2) DEFAULT 80.00,
    shuffle_questions BOOLEAN DEFAULT FALSE,
    status ENUM('draft','published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_course (course_id),
    INDEX idx_module (module_id),
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS quiz_questions (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    quiz_id INT UNSIGNED NOT NULL,
    tenant_id INT UNSIGNED NOT NULL,
    text TEXT NOT NULL,
    position INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_quiz_position (quiz_id, position),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS quiz_choices (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    question_id INT UNSIGNED NOT NULL,
    text VARCHAR(500) NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    position INT UNSIGNED DEFAULT 0,
    INDEX idx_question (question_id),
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS quiz_attempts (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    quiz_id INT UNSIGNED NOT NULL,
    score_percentage DECIMAL(5,2) NOT NULL,
    passed BOOLEAN DEFAULT FALSE,
    answers JSON DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_quiz (user_id, quiz_id),
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS certificates (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    certificate_number VARCHAR(50) NOT NULL,
    score_percentage DECIMAL(5,2) DEFAULT NULL,
    pdf_path VARCHAR(500) DEFAULT NULL,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    revoked_at TIMESTAMP NULL DEFAULT NULL,
    revocation_reason VARCHAR(500) DEFAULT NULL,
    UNIQUE KEY unique_cert_number (certificate_number),
    UNIQUE KEY unique_user_course (user_id, course_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_user (user_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS lesson_attachments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    lesson_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50) DEFAULT NULL,
    file_size BIGINT UNSIGNED DEFAULT 0,
    download_count INT UNSIGNED DEFAULT 0,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lesson (lesson_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(500) DEFAULT NULL,
    message TEXT NOT NULL,
    status ENUM('unread','read','replied') DEFAULT 'unread',
    admin_reply TEXT DEFAULT NULL,
    replied_at TIMESTAMP NULL DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_tenant_created (tenant_id, created_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- ============================================
-- PHASE 2: NEWSLETTERS, EMAIL SEQUENCES,
-- INVOICES, DISCOUNTS, COMPANIES,
-- CONSULTATIONS, MASTERMIND PROGRAMS
-- ============================================

CREATE TABLE IF NOT EXISTS newsletters (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    subject VARCHAR(500) NOT NULL,
    body_html LONGTEXT DEFAULT NULL,
    status ENUM('draft','sending','sent','failed') DEFAULT 'draft',
    sent_at TIMESTAMP NULL DEFAULT NULL,
    recipient_count INT UNSIGNED DEFAULT 0,
    failed_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS email_sequences (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    trigger_type ENUM('quiz_completion','lead_magnet_signup','purchase','course_enrollment','manual') NOT NULL DEFAULT 'manual',
    trigger_id INT UNSIGNED DEFAULT NULL,
    status ENUM('draft','active','paused') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_trigger (trigger_type, trigger_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS email_sequence_steps (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    sequence_id INT UNSIGNED NOT NULL,
    day_number INT UNSIGNED NOT NULL DEFAULT 1,
    subject VARCHAR(500) NOT NULL,
    body_html TEXT DEFAULT NULL,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sequence_sort (sequence_id, sort_order),
    FOREIGN KEY (sequence_id) REFERENCES email_sequences(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS email_sequence_enrollments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    sequence_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(255) DEFAULT NULL,
    status ENUM('active','completed','cancelled') DEFAULT 'active',
    current_step INT UNSIGNED DEFAULT 0,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_sequence_status (sequence_id, status),
    INDEX idx_email (email),
    FOREIGN KEY (sequence_id) REFERENCES email_sequences(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS email_sequence_logs (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    enrollment_id INT UNSIGNED NOT NULL,
    step_id INT UNSIGNED NOT NULL,
    status ENUM('sent','failed') DEFAULT 'sent',
    error_message TEXT DEFAULT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_enrollment (enrollment_id),
    FOREIGN KEY (enrollment_id) REFERENCES email_sequence_enrollments(id) ON DELETE CASCADE,
    FOREIGN KEY (step_id) REFERENCES email_sequence_steps(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS discount_codes (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    code VARCHAR(50) NOT NULL,
    type ENUM('percentage','fixed') NOT NULL DEFAULT 'percentage',
    value DECIMAL(10,2) NOT NULL,
    min_order_dkk DECIMAL(10,2) DEFAULT NULL,
    max_uses INT UNSIGNED DEFAULT NULL,
    used_count INT UNSIGNED DEFAULT 0,
    applies_to ENUM('all','courses','products','ebooks') DEFAULT 'all',
    expires_at TIMESTAMP NULL DEFAULT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_code_tenant (code, tenant_id),
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS discount_code_uses (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    discount_code_id INT UNSIGNED NOT NULL,
    order_id INT UNSIGNED DEFAULT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    amount_dkk DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (discount_code_id) REFERENCES discount_codes(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS company_accounts (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    admin_user_id INT UNSIGNED NOT NULL,
    total_licenses INT UNSIGNED DEFAULT 0,
    status ENUM('active','suspended','cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS team_members (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_account_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(255) DEFAULT NULL,
    status ENUM('pending','invited','active','deactivated') DEFAULT 'pending',
    invited_at TIMESTAMP NULL DEFAULT NULL,
    joined_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_company (company_account_id),
    INDEX idx_user (user_id),
    INDEX idx_email (email),
    FOREIGN KEY (company_account_id) REFERENCES company_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS company_course_licenses (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_account_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    seats_total INT UNSIGNED NOT NULL DEFAULT 1,
    seats_used INT UNSIGNED DEFAULT 0,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_company_course (company_account_id, course_id),
    FOREIGN KEY (company_account_id) REFERENCES company_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS consultation_types (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    duration_minutes INT UNSIGNED NOT NULL DEFAULT 60,
    price_dkk DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('active','inactive') DEFAULT 'active',
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS consultation_bookings (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    type_id INT UNSIGNED DEFAULT NULL,
    booking_number VARCHAR(50) NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) DEFAULT NULL,
    company VARCHAR(255) DEFAULT NULL,
    project_description TEXT DEFAULT NULL,
    preferred_date DATE DEFAULT NULL,
    preferred_time VARCHAR(20) DEFAULT NULL,
    urgency ENUM('low','medium','high') DEFAULT 'medium',
    status ENUM('pending','confirmed','completed','cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid','paid','refunded') DEFAULT 'unpaid',
    stripe_payment_intent_id VARCHAR(255) DEFAULT NULL,
    admin_notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_booking_tenant (booking_number, tenant_id),
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (type_id) REFERENCES consultation_types(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS mastermind_programs (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description LONGTEXT DEFAULT NULL,
    short_description TEXT DEFAULT NULL,
    cover_image_path VARCHAR(500) DEFAULT NULL,
    status ENUM('draft','published','archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slug_tenant (slug, tenant_id),
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS mastermind_tiers (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    program_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    upfront_price_dkk DECIMAL(10,2) DEFAULT 0.00,
    monthly_price_dkk DECIMAL(10,2) DEFAULT 0.00,
    max_members INT UNSIGNED DEFAULT NULL,
    stripe_price_id VARCHAR(255) DEFAULT NULL,
    features JSON DEFAULT NULL,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_program (program_id),
    FOREIGN KEY (program_id) REFERENCES mastermind_programs(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS mastermind_enrollments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    program_id INT UNSIGNED NOT NULL,
    tier_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    enrollment_number VARCHAR(50) NOT NULL,
    status ENUM('active','paused','cancelled','completed') DEFAULT 'active',
    stripe_customer_id VARCHAR(255) DEFAULT NULL,
    stripe_subscription_id VARCHAR(255) DEFAULT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cancelled_at TIMESTAMP NULL DEFAULT NULL,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY unique_enrollment (enrollment_number),
    INDEX idx_tenant (tenant_id),
    INDEX idx_program_user (program_id, user_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES mastermind_programs(id) ON DELETE CASCADE,
    FOREIGN KEY (tier_id) REFERENCES mastermind_tiers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS mastermind_milestones (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    enrollment_id INT UNSIGNED NOT NULL,
    milestone_type VARCHAR(100) NOT NULL,
    notes TEXT DEFAULT NULL,
    achieved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_enrollment (enrollment_id),
    FOREIGN KEY (enrollment_id) REFERENCES mastermind_enrollments(id) ON DELETE CASCADE
);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- SEED DATA: Default plans
-- ============================================

INSERT INTO plans (name, slug, price_monthly_usd, price_yearly_usd, max_customers, max_leads, max_campaigns, max_products, max_lead_magnets, features_json, sort_order) VALUES
('Starter', 'starter', 79.00, 780.00, 100, 500, 2, 20, 5, '{"blog": true, "ebooks": true, "lead_magnets": true, "orders": false, "connectpilot": false}', 1),
('Growth', 'growth', 149.00, 1500.00, 500, 2000, 10, 100, 20, '{"blog": true, "ebooks": true, "lead_magnets": true, "orders": true, "connectpilot": true}', 2),
('Enterprise', 'enterprise', 299.00, 2988.00, NULL, NULL, NULL, NULL, NULL, '{"blog": true, "ebooks": true, "lead_magnets": true, "orders": true, "connectpilot": true, "custom_domain": true, "priority_support": true}', 3);

-- Create default superadmin (password: change-me-immediately)
INSERT INTO users (tenant_id, role, name, email, password_hash, status) VALUES
(NULL, 'superadmin', 'Platform Admin', 'admin@kompaza.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');
