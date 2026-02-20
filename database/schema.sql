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
    feature_leadshark BOOLEAN DEFAULT FALSE,
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
    source_type ENUM('lead_magnet','ebook','newsletter','article') NOT NULL,
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
-- PHASE 5: LEADSHARK - LINKEDIN AUTOMATION
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

CREATE TABLE IF NOT EXISTS leadshark_campaigns (
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

CREATE TABLE IF NOT EXISTS leadshark_sequence_steps (
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
    FOREIGN KEY (campaign_id) REFERENCES leadshark_campaigns(id) ON DELETE CASCADE
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
    FOREIGN KEY (campaign_id) REFERENCES leadshark_campaigns(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS leadshark_activity_log (
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

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- SEED DATA: Default plans
-- ============================================

INSERT INTO plans (name, slug, price_monthly_usd, price_yearly_usd, max_customers, max_leads, max_campaigns, max_products, max_lead_magnets, features_json, sort_order) VALUES
('Starter', 'starter', 79.00, 780.00, 100, 500, 2, 20, 5, '{"blog": true, "ebooks": true, "lead_magnets": true, "orders": false, "leadshark": false}', 1),
('Growth', 'growth', 149.00, 1500.00, 500, 2000, 10, 100, 20, '{"blog": true, "ebooks": true, "lead_magnets": true, "orders": true, "leadshark": true}', 2),
('Enterprise', 'enterprise', 299.00, 2988.00, NULL, NULL, NULL, NULL, NULL, '{"blog": true, "ebooks": true, "lead_magnets": true, "orders": true, "leadshark": true, "custom_domain": true, "priority_support": true}', 3);

-- Create default superadmin (password: change-me-immediately)
INSERT INTO users (tenant_id, role, name, email, password_hash, status) VALUES
(NULL, 'superadmin', 'Platform Admin', 'admin@kompaza.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');
